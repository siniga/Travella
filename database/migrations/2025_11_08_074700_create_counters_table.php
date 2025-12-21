<?php

// database/migrations/2025_01_01_000000_create_counters_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('counters', function (Blueprint $table) {
            $table->id();
            $table->string('key');          // e.g., "draft"
            $table->integer('year');        // e.g., 2025
            $table->unsignedInteger('value')->default(0);
            $table->timestamps();
            $table->unique(['key','year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('counters');
    }
};

