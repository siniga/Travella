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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('draft_id', 50)->unique(); // "DRAFT-2025-001"
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['pending_payment', 'paid', 'processing', 'completed', 'cancelled'])->default('pending_payment');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->string('discount_code', 50)->nullable();
            $table->decimal('total_amount', 12, 2);
            $table->string('currency', 3);
            $table->string('source', 50)->nullable(); // "mobile_app"
            $table->string('platform', 50)->nullable(); // "react_native"
            $table->json('metadata')->nullable(); // Additional order metadata
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index('draft_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
