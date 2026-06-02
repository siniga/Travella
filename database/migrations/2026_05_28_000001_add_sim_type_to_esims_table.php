<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('esims', function (Blueprint $table) {
            $table->enum('sim_type', ['esim', 'physical'])
                ->default('physical')
                ->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('esims', function (Blueprint $table) {
            $table->dropColumn('sim_type');
        });
    }
};
