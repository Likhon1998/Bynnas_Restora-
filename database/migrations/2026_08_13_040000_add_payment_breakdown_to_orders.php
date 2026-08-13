<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'payment_method')) {
                $table->string('payment_method', 32)->nullable()->after('payment_status');
            }
            if (! Schema::hasColumn('orders', 'cash_paid')) {
                $table->decimal('cash_paid', 12, 2)->default(0)->after('payment_method');
            }
            if (! Schema::hasColumn('orders', 'bkash_paid')) {
                $table->decimal('bkash_paid', 12, 2)->default(0)->after('cash_paid');
            }
            if (! Schema::hasColumn('orders', 'card_paid')) {
                $table->decimal('card_paid', 12, 2)->default(0)->after('bkash_paid');
            }
            if (! Schema::hasColumn('orders', 'amount_tendered')) {
                $table->decimal('amount_tendered', 12, 2)->default(0)->after('card_paid');
            }
            if (! Schema::hasColumn('orders', 'change_amount')) {
                $table->decimal('change_amount', 12, 2)->default(0)->after('amount_tendered');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            foreach (['change_amount', 'amount_tendered', 'card_paid', 'bkash_paid', 'cash_paid', 'payment_method'] as $col) {
                if (Schema::hasColumn('orders', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
