<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('esims', function (Blueprint $table) {
            if (! Schema::hasColumn('esims', 'import_batch_id')) {
                $table->foreignId('import_batch_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('esim_import_batches')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('esims', function (Blueprint $table) {
            if (Schema::hasColumn('esims', 'import_batch_id')) {
                $table->dropConstrainedForeignId('import_batch_id');
            }
        });
    }
};
