<?php

use App\Models\Esim;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('esims', function (Blueprint $table) {
            if (! Schema::hasColumn('esims', 'provider_status')) {
                $table->enum('provider_status', [Esim::PROVIDER_STATUS_ACTIVE, Esim::PROVIDER_STATUS_SUSPENDED])
                    ->default(Esim::PROVIDER_STATUS_ACTIVE)
                    ->after('sim_type');
                $table->index('provider_status');
            }
        });

        DB::table('esims')
            ->whereNull('provider_status')
            ->update(['provider_status' => Esim::PROVIDER_STATUS_ACTIVE]);
    }

    public function down(): void
    {
        if (! Schema::hasColumn('esims', 'provider_status')) {
            return;
        }

        Schema::table('esims', function (Blueprint $table) {
            $table->dropIndex(['provider_status']);
            $table->dropColumn('provider_status');
        });
    }
};

