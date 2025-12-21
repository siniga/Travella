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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['bundle', 'service']);
            $table->foreignId('bundle_id')->nullable()->constrained()->nullOnDelete();
            $table->string('bundle_name', 120)->nullable();
            $table->unsignedBigInteger('data_amount')->nullable(); // in MB
            $table->unsignedInteger('validity_days')->nullable();
            $table->decimal('price', 12, 2);
            $table->string('currency', 3);
            $table->json('metadata')->nullable(); // Additional item metadata
            $table->timestamps();
            
            $table->index(['order_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
