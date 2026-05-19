<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'recharge_status')) {
                $table->string('recharge_status', 30)->nullable()->after('paid_at');
            }
            if (! Schema::hasColumn('orders', 'recharge_reference')) {
                $table->string('recharge_reference', 100)->nullable()->after('recharge_status');
            }
            if (! Schema::hasColumn('orders', 'recharge_transaction_id')) {
                $table->string('recharge_transaction_id', 100)->nullable()->after('recharge_reference');
            }
            if (! Schema::hasColumn('orders', 'recharge_response')) {
                $table->json('recharge_response')->nullable()->after('recharge_transaction_id');
            }
            if (! Schema::hasColumn('orders', 'recharge_completed_at')) {
                $table->timestamp('recharge_completed_at')->nullable()->after('recharge_response');
            }
            if (! Schema::hasColumn('orders', 'recharge_http_status')) {
                $table->unsignedSmallInteger('recharge_http_status')->nullable()->after('recharge_completed_at');
            }

            $table->index('recharge_status');
            $table->index('recharge_reference');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $columns = [
                'recharge_http_status',
                'recharge_completed_at',
                'recharge_response',
                'recharge_transaction_id',
                'recharge_reference',
                'recharge_status',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
