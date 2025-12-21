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
        Schema::create('bundle_types', function (Blueprint $t) {
            $t->id();
            $t->string('code', 20)->unique();   // DATA, VOICE, SMS, COMBO
            $t->string('name', 50);             // Data only, Voice only, etc.
            $t->timestamps();
          });
          
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bundle_types');
    }
};
