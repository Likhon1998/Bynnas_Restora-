<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('menu_hero_url')->nullable()->after('social_youtube');
            $table->string('menu_eyebrow', 120)->nullable()->after('menu_hero_url');
            $table->string('menu_title', 160)->nullable()->after('menu_eyebrow');
            $table->text('menu_subtitle')->nullable()->after('menu_title');
            $table->string('menu_list_title', 160)->nullable()->after('menu_subtitle');
            $table->string('menu_allergen_title', 120)->nullable()->after('menu_list_title');
            $table->text('menu_allergen_text')->nullable()->after('menu_allergen_title');
            $table->string('menu_fresh_title', 120)->nullable()->after('menu_allergen_text');
            $table->text('menu_fresh_text')->nullable()->after('menu_fresh_title');
            $table->string('menu_fresh_image_url')->nullable()->after('menu_fresh_text');
            $table->string('menu_special_title', 255)->nullable()->after('menu_fresh_image_url');
            $table->string('menu_special_cta', 80)->nullable()->after('menu_special_title');
            $table->string('menu_special_image_url')->nullable()->after('menu_special_cta');
        });

        $defaults = [
            'menu_hero_url' => 'https://images.unsplash.com/photo-1551183053-bf91a1d81141?auto=format&fit=crop&w=1800&q=85',
            'menu_eyebrow' => 'Delicious Choices, Made for You',
            'menu_title' => 'Our Menu',
            'menu_subtitle' => 'Explore chef-crafted dishes made with fresh ingredients — from comfort classics to signature favorites, prepared with care every day.',
            'menu_list_title' => 'Our Delicious Menu',
            'menu_allergen_title' => 'Food Allergies?',
            'menu_allergen_text' => 'Tell us about any allergies and we’ll help you choose safely.',
            'menu_fresh_title' => 'Fresh Ingredients',
            'menu_fresh_text' => 'We source seasonal produce daily so every plate tastes vibrant and clean.',
            'menu_fresh_image_url' => 'https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=800&q=85',
            'menu_special_title' => 'Looking for something special? Our chefs are ready to create something amazing just for you.',
            'menu_special_cta' => 'Special Request',
            'menu_special_image_url' => 'https://images.unsplash.com/photo-1600891964092-4316c288032e?auto=format&fit=crop&w=1600&q=85',
        ];

        if (Schema::hasTable('site_settings')) {
            DB::table('site_settings')->update($defaults);
        }
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'menu_hero_url', 'menu_eyebrow', 'menu_title', 'menu_subtitle', 'menu_list_title',
                'menu_allergen_title', 'menu_allergen_text', 'menu_fresh_title', 'menu_fresh_text',
                'menu_fresh_image_url', 'menu_special_title', 'menu_special_cta', 'menu_special_image_url',
            ]);
        });
    }
};
