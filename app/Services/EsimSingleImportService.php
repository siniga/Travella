<?php

namespace App\Services;

use App\Models\Esim;
use App\Models\EsimImportBatch;
use App\Models\EsimImportItem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Smalot\PdfParser\Page;
use Smalot\PdfParser\Parser;
use Smalot\PdfParser\XObject\Image;
use Zxing\QrReader;

class EsimSingleImportService
{
    private const QR_STORAGE_DIR = 'esims/qr-codes';

    private const SOURCE_STORAGE_DIR = 'esims/import-sources';

    /**
     * Process one uploaded file for a batch import item.
     *
     * @return array{esim: Esim, created: bool}
     */
    public function process(
        EsimImportBatch $batch,
        EsimImportItem $item,
        UploadedFile $file,
        ?string $phoneOverride = null,
        ?string $iccidOverride = null,
    ): array {
        $sourcePath = $this->storeSourceFile($batch->id, $item->id, $file);
        $item->update(['source_file_path' => $sourcePath]);

        $mime = strtolower($file->getMimeType() ?? '');
        $extension = strtolower($file->getClientOriginalExtension() ?: 'bin');
        $isPdf = $extension === 'pdf' || str_contains($mime, 'pdf');

        $text = '';
        $qrBinary = null;
        $qrExtension = 'png';

        if ($isPdf) {
            $page = $this->loadSinglePdfPage($file);
            $text = trim($page->getText());
            $qrPayload = $this->extractQrFromPdfPage($page);
            $qrBinary = $qrPayload['binary'];
            $qrExtension = $qrPayload['extension'];
        } else {
            $qrBinary = file_get_contents($file->getRealPath()) ?: null;
            $qrExtension = in_array($extension, ['jpg', 'jpeg'], true) ? 'jpg' : 'png';
        }

        $phoneNumber = $phoneOverride
            ? Esim::normalizeMsisdn($phoneOverride)
            : $this->extractPhoneNumber($text);

        $qrCodeData = null;
        if ($qrBinary) {
            $qrCodeData = $this->decodeQrFromBinary($qrBinary);
            if (! $phoneNumber && $qrCodeData) {
                $phoneNumber = $this->extractPhoneNumber($qrCodeData) ?? $this->extractPhoneFromQrData($qrCodeData);
            }
            if (! $iccidOverride && $qrCodeData) {
                $iccidOverride = $this->extractIccid($qrCodeData) ?? $iccidOverride;
            }
        }

        if (! $phoneNumber) {
            throw new \RuntimeException('Phone number not found.');
        }

        $iccid = $iccidOverride ?: $this->extractIccid($text);

        $qrPath = null;
        if ($qrBinary) {
            $qrPath = $this->storeQrImage($phoneNumber, $qrBinary, $qrExtension);
            $item->update(['qr_code_path' => $qrPath]);
        }

        $existing = Esim::query()->where('msisdn', $phoneNumber)->first();

        $attributes = array_filter([
            'import_batch_id' => $batch->id,
            'iccid' => $iccid,
            'qr_code_path' => $qrPath,
            'qr_code_data' => $qrCodeData,
            'sim_type' => Esim::SIM_TYPE_ESIM,
            'provider_status' => Esim::PROVIDER_STATUS_ACTIVE,
            'description' => 'Imported via batch #'.$batch->id,
        ], fn ($value) => $value !== null && $value !== '');

        if (! $existing) {
            $attributes['status'] = 'AVAILABLE';
            $attributes['sale_status'] = Esim::SALE_STATUS_AVAILABLE;
            $attributes['network_id'] = 1;
        } else {
            if ($qrPath && $existing->qr_code_path && $existing->qr_code_path !== $qrPath) {
                Storage::disk('local')->delete($existing->qr_code_path);
            }
            if (! $iccid && $existing->iccid) {
                unset($attributes['iccid']);
            }
            if (! $qrCodeData && $existing->qr_code_data) {
                unset($attributes['qr_code_data']);
            }
            if (! $qrPath && $existing->qr_code_path) {
                unset($attributes['qr_code_path']);
            }
        }

        $esim = Esim::query()->updateOrCreate(
            ['msisdn' => $phoneNumber],
            $attributes,
        );

        return [
            'esim' => $esim->fresh(),
            'created' => $existing === null,
        ];
    }

    /**
     * Re-process a failed item using stored source file or a new upload.
     *
     * @return array{esim: Esim, created: bool}
     */
    public function retry(
        EsimImportBatch $batch,
        EsimImportItem $item,
        ?UploadedFile $file = null,
        ?string $phoneOverride = null,
        ?string $iccidOverride = null,
    ): array {
        if ($file) {
            return $this->process($batch, $item, $file, $phoneOverride, $iccidOverride);
        }

        if (! $item->source_file_path || ! Storage::disk('local')->exists($item->source_file_path)) {
            throw new \RuntimeException('No source file available for retry. Upload a new file.');
        }

        $tempPath = storage_path('app/private/esims/tmp/retry-'.$item->id.'-'.Str::uuid());
        @mkdir(dirname($tempPath), 0755, true);
        file_put_contents($tempPath, Storage::disk('local')->get($item->source_file_path));

        $uploaded = new UploadedFile(
            $tempPath,
            basename($item->source_file_path),
            null,
            null,
            true,
        );

        try {
            return $this->process(
                $batch,
                $item,
                $uploaded,
                $phoneOverride ?? $item->phone_number,
                $iccidOverride ?? $item->iccid,
            );
        } finally {
            @unlink($tempPath);
        }
    }

    public function extractPhoneNumber(string $text): ?string
    {
        $patterns = [
            '/(?:\+?255|00255)(7\d{8})/',
            '/\b(2557\d{8})\b/',
            '/\b(0?7\d{8})\b/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $digits = preg_replace('/\D+/', '', $matches[1]) ?? '';

                if (str_starts_with($digits, '0')) {
                    $digits = '255'.substr($digits, 1);
                } elseif (str_starts_with($digits, '7') && strlen($digits) === 9) {
                    $digits = '255'.$digits;
                }

                if (strlen($digits) >= 12) {
                    return Esim::normalizeMsisdn($digits);
                }
            }
        }

        return null;
    }

    public function extractIccid(string $text): ?string
    {
        if (preg_match('/\b(89[\dA-Fa-f]{17,18})\b/', $text, $matches)) {
            return strtoupper($matches[1]);
        }

        return null;
    }

    private function extractPhoneFromQrData(string $qrData): ?string
    {
        if (preg_match('/(?:msisdn|phone|tel)[=:]?\s*(\+?2557\d{8}|2557\d{8})/i', $qrData, $matches)) {
            return Esim::normalizeMsisdn($matches[1]);
        }

        return null;
    }

    private function loadSinglePdfPage(UploadedFile $file): Page
    {
        $parser = new Parser();
        $document = $parser->parseFile($file->getRealPath());
        $pages = $document->getPages();

        if ($pages === []) {
            throw new \RuntimeException('PDF contains no pages.');
        }

        return $pages[0];
    }

    /**
     * @return array{data: ?string, binary: ?string, extension: string}
     */
    private function extractQrFromPdfPage(Page $page): array
    {
        foreach ($this->extractImagesFromPage($page) as $image) {
            $decoded = $this->decodeQrFromBinary($image['binary']);
            if ($decoded !== null) {
                return [
                    'data' => $decoded,
                    'binary' => $image['binary'],
                    'extension' => $image['extension'],
                ];
            }
        }

        return ['data' => null, 'binary' => null, 'extension' => 'png'];
    }

    /**
     * @return list<array{binary: string, extension: string}>
     */
    private function extractImagesFromPage(Page $page): array
    {
        $images = [];

        try {
            foreach ($page->getXObjects() as $xObject) {
                if (! $xObject instanceof Image) {
                    continue;
                }

                $binary = $xObject->getContent();
                if (! is_string($binary) || $binary === '') {
                    continue;
                }

                $images[] = [
                    'binary' => $binary,
                    'extension' => $this->guessImageExtension($binary),
                ];
            }
        } catch (\Throwable $e) {
            Log::debug('eSIM single import: XObject extraction failed', ['error' => $e->getMessage()]);
        }

        return $images;
    }

    private function decodeQrFromBinary(string $binary): ?string
    {
        try {
            $reader = new QrReader($binary, QrReader::SOURCE_TYPE_BLOB);
            $text = $reader->text();

            return is_string($text) && trim($text) !== '' ? trim($text) : null;
        } catch (\Throwable) {
            $tempPath = storage_path('app/private/esims/tmp/'.Str::uuid().'.png');
            @mkdir(dirname($tempPath), 0755, true);

            try {
                file_put_contents($tempPath, $binary);
                $reader = new QrReader($tempPath, QrReader::SOURCE_TYPE_FILE);
                $text = $reader->text();

                return is_string($text) && trim($text) !== '' ? trim($text) : null;
            } catch (\Throwable) {
                return null;
            } finally {
                @unlink($tempPath);
            }
        }
    }

    private function storeSourceFile(int $batchId, int $itemId, UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: 'bin');
        $path = self::SOURCE_STORAGE_DIR.'/'.$batchId.'/'.$itemId.'.'.$extension;

        Storage::disk('local')->put($path, file_get_contents($file->getRealPath()) ?: '');

        return $path;
    }

    private function storeQrImage(string $phoneNumber, string $binary, string $extension): string
    {
        $safePhone = preg_replace('/\D+/', '', $phoneNumber) ?? 'unknown';
        $ext = $extension === 'jpg' || $extension === 'jpeg' ? 'jpg' : 'png';
        $path = self::QR_STORAGE_DIR.'/'.$safePhone.'.'.$ext;

        Storage::disk('local')->put($path, $binary);

        return $path;
    }

    private function guessImageExtension(string $binary): string
    {
        if (str_starts_with($binary, "\x89PNG")) {
            return 'png';
        }

        if (str_starts_with($binary, "\xFF\xD8\xFF")) {
            return 'jpg';
        }

        return 'png';
    }
}
