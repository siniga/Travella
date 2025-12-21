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
        Schema::create('countries', function (Blueprint $t) {
            $t->id();
            $t->string('name', 100);
            $t->string('iso2', 2)->unique();        // e.g., TZ, KE, UG
            $t->string('iso3', 3)->unique();        // e.g., TZA
            $t->string('currency', 3)->default('TZS');
            $t->timestamps();
          });
          
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
