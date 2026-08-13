<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            $table->decimal('packaging_cost', 12, 2)->default(0)->after('selling_price');
            $table->decimal('other_cost', 12, 2)->default(0)->after('packaging_cost');
        });
    }

    public function down(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            $table->dropColumn(['packaging_cost', 'other_cost']);
        });
    }
};
