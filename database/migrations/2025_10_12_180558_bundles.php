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
        Schema::create('bundles', function (Blueprint $t) {
            $t->id();
            $t->foreignId('bundle_type_id')->constrained()->cascadeOnDelete();
            $t->foreignId('country_provider_id')->constrained('country_provider')->cascadeOnDelete();
          
            $t->string('name', 120);               // e.g., Daily 1GB, Weekly 5GB
            $t->unsignedInteger('validity_days');  // duration
            // Quantities are nullable; use the one(s) relevant to type
            $t->unsignedBigInteger('data_mb')->nullable();    // MBs
            $t->unsignedInteger('voice_minutes')->nullable();
            $t->unsignedInteger('sms')->nullable();
          
            $t->decimal('price', 12, 2);           // price in local currency
            $t->string('currency', 3);             // e.g., TZS
            $t->boolean('active')->default(true);
            $t->json('metadata')->nullable();      // internal product codes, terms
            $t->timestamps();
          
            $t->index(['country_provider_id','bundle_type_id','active']);
          });
          
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bundles');
    }
};
