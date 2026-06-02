<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('agents') && ! Schema::hasTable('agent_location')) {
            Schema::rename('agents', 'agent_location');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('agent_location') && ! Schema::hasTable('agents')) {
            Schema::rename('agent_location', 'agents');
        }
    }
};
