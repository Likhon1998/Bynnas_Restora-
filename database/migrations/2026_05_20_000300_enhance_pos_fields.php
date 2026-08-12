<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->string('badge')->nullable()->after('image_url');
            $table->boolean('is_favorite')->default(false)->after('badge');
            $table->boolean('is_bestseller')->default(false)->after('is_favorite');
            $table->unsignedInteger('sort_order')->default(0)->after('is_bestseller');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedTinyInteger('guest_count')->nullable()->after('meta');
            $table->decimal('service_charge', 12, 2)->default(0)->after('subtotal');
            $table->decimal('tax_amount', 12, 2)->default(0)->after('service_charge');
            $table->decimal('tip_amount', 12, 2)->default(0)->after('tax_amount');
            $table->string('promo_code')->nullable()->after('tip_amount');
            $table->decimal('discount_amount', 12, 2)->default(0)->after('promo_code');
            $table->boolean('is_held')->default(false)->after('payment_status');
            $table->json('tags')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn(['badge', 'is_favorite', 'is_bestseller', 'sort_order']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'guest_count', 'service_charge', 'tax_amount', 'tip_amount',
                'promo_code', 'discount_amount', 'is_held', 'tags',
            ]);
        });
    }
};
