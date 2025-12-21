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
        Schema::create('providers', function (Blueprint $t) {
            $t->id();
            $t->string('name', 120);                // TTCL, Airtel, Vodacom, etc.
            $t->string('slug', 120)->unique();      // ttcl, airtel, vodacom
            $t->json('metadata')->nullable();       // logos, APNs, etc.
            $t->timestamps();
          });
          
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('providers');
    }
};
