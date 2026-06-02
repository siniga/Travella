<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_location', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('phone', 32);
            $table->string('work_station', 255);
            $table->string('current_location', 255)->nullable();
            $table->timestamp('current_location_updated_at')->nullable();
            $table->timestamps();

            $table->index('work_station');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_location');
    }
};
