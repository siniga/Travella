<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('esim_import_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('esim_import_batch_id')->constrained('esim_import_batches')->cascadeOnDelete();
            $table->foreignId('esim_id')->nullable()->constrained('esims')->nullOnDelete();
            $table->unsignedInteger('page_number')->nullable();
            $table->string('phone_number', 30)->nullable();
            $table->string('iccid', 50)->nullable();
            $table->string('source_file_path')->nullable();
            $table->string('qr_code_path')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['esim_import_batch_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('esim_import_items');
    }
};
