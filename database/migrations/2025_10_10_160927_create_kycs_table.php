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
        Schema::create('kycs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            // store PII encrypted at rest
            $t->text('passport_id_encrypted')->nullable();   // encrypted blob
            $t->string('passport_country', 2)->nullable(); // optional ISO-3166-1 alpha-2
            $t->date('arrival_date');
            $t->date('departure_date');
            $t->string('reason', 100)->nullable(); // tourism/business/etc.
            $t->string('passport_hash', 64)->nullable(); // hash for audit/search purposes
            $t->timestamp('verified_at')->nullable(); // if you add manual review later
            $t->timestamps();
            $t->unique('user_id'); // 1 KYC record per user (adjust if you want multiples)
          });
          
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kycs');
    }
};
