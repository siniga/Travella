<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('country_provider', function (Blueprint $t) {
            $t->id();
            $t->foreignId('country_id')->constrained()->cascadeOnDelete();
            $t->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $t->boolean('is_default')->default(false);   // e.g., TTCL default in TZ
            $t->json('settings')->nullable();            // country-specific codes, prefixes
            $t->unique(['country_id','provider_id']);
            $t->timestamps();
          });
          
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('country_provider');
    }
};
