<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Homepage / public website CMS fields on site_settings.
 * Defaults match the current marketing homepage visuals.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('logo_url')->nullable()->after('footer_note');
            $table->string('hero_bg_url')->nullable()->after('logo_url');
            $table->string('hero_image_url')->nullable()->after('hero_bg_url');
            $table->string('hero_eyebrow', 120)->nullable()->after('hero_image_url');
            $table->string('hero_headline', 160)->nullable()->after('hero_eyebrow');
            $table->string('hero_headline_accent', 160)->nullable()->after('hero_headline');
            $table->text('hero_description')->nullable()->after('hero_headline_accent');
            $table->string('popular_eyebrow', 80)->nullable()->after('hero_description');
            $table->string('popular_title', 120)->nullable()->after('popular_eyebrow');
            $table->text('popular_subtitle')->nullable()->after('popular_title');
            $table->string('story_eyebrow', 80)->nullable()->after('popular_subtitle');
            $table->string('story_title', 160)->nullable()->after('story_eyebrow');
            $table->text('story_body')->nullable()->after('story_title');
            $table->string('story_chef_url')->nullable()->after('story_body');
            $table->string('story_interior_url')->nullable()->after('story_chef_url');
            $table->string('story_food_url')->nullable()->after('story_interior_url');
            $table->string('story_years_label', 40)->nullable()->after('story_food_url');
            $table->string('story_chef_name', 80)->nullable()->after('story_years_label');
            $table->string('story_chef_role', 80)->nullable()->after('story_chef_name');
            $table->string('story_customers_label', 40)->nullable()->after('story_chef_role');
            $table->json('story_points')->nullable()->after('story_customers_label');
            $table->json('hero_features')->nullable()->after('story_points');
            $table->json('value_props')->nullable()->after('hero_features');
            $table->json('testimonials')->nullable()->after('value_props');
            $table->json('gallery_images')->nullable()->after('testimonials');
            $table->string('instagram_handle', 80)->nullable()->after('gallery_images');
            $table->string('social_facebook')->nullable()->after('instagram_handle');
            $table->string('social_instagram')->nullable()->after('social_facebook');
            $table->string('social_twitter')->nullable()->after('social_instagram');
            $table->string('social_youtube')->nullable()->after('social_twitter');
        });

        $defaults = [
            'logo_url' => null,
            'hero_bg_url' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=2200&q=85',
            'hero_image_url' => 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=1200&q=90',
            'hero_eyebrow' => 'Welcome to Fine Dining',
            'hero_headline' => 'Good Food,',
            'hero_headline_accent' => 'Great Moments',
            'hero_description' => 'Delicious meals made with fresh ingredients and passion. Experience the perfect blend of taste and tradition.',
            'popular_eyebrow' => 'Our Specialties',
            'popular_title' => 'Popular Dishes',
            'popular_subtitle' => 'Signature plates guests love most — crafted fresh daily with bold flavor and beautiful presentation.',
            'story_eyebrow' => 'Our Story',
            'story_title' => 'A Passion for Great Food',
            'story_body' => "Bynnas Restora began with a simple belief: great meals create lasting memories. From our kitchen to your table, we blend tradition with modern craft to deliver dishes that feel both familiar and exciting.\n\nEvery plate is prepared with care, every guest is welcomed like family, and every evening is designed around comfort, flavor, and hospitality.",
            'story_chef_url' => 'https://images.unsplash.com/photo-1556910103-1c02745aae4d?auto=format&fit=crop&w=1000&q=85',
            'story_interior_url' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=800&q=85',
            'story_food_url' => 'https://images.unsplash.com/photo-1513104890138-7c749659a591?auto=format&fit=crop&w=800&q=85',
            'story_years_label' => '15+',
            'story_chef_name' => 'Chef Bynnas',
            'story_chef_role' => 'Founder & Head Chef',
            'story_customers_label' => '25K+',
            'story_points' => json_encode([
                'Fresh ingredients sourced daily from trusted local partners',
                'Recipes crafted with passion and refined over 15 years',
                'Warm hospitality that makes every guest feel at home',
                'Consistent quality from kitchen to table, every service',
            ]),
            'hero_features' => json_encode([
                ['title' => 'Fresh Ingredients', 'subtitle' => 'Locally Sourced', 'key' => 'leaf'],
                ['title' => 'Expert Chefs', 'subtitle' => '10+ Years Experience', 'key' => 'chef'],
                ['title' => 'Best Quality', 'subtitle' => 'Premium Foods', 'key' => 'award'],
                ['title' => 'Hygienic & Safe', 'subtitle' => 'Highest Standards', 'key' => 'shield'],
            ]),
            'value_props' => json_encode([
                ['title' => 'Fast & Easy Booking', 'text' => 'Reserve your table online in seconds with our simple booking flow.', 'key' => 'calendar'],
                ['title' => 'Fresh & Healthy Food', 'text' => 'Seasonal produce and carefully sourced ingredients in every dish.', 'key' => 'salad'],
                ['title' => 'Cozy & Friendly Place', 'text' => 'Warm ambiance and welcoming service for memorable evenings.', 'key' => 'home'],
                ['title' => 'Hygienic & Safe', 'text' => 'Strict kitchen standards so you can dine with complete confidence.', 'key' => 'shield'],
            ]),
            'testimonials' => json_encode([
                [
                    'id' => 1,
                    'quote' => 'Absolutely incredible! The steak was perfectly cooked and the ambiance made our anniversary unforgettable. Bynnas Restora is our new favorite spot in the city.',
                    'name' => 'Sarah Johnson',
                    'role' => 'Food Blogger',
                    'avatar' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=200&q=85',
                ],
                [
                    'id' => 2,
                    'quote' => 'From booking to dessert, everything felt premium. Fresh ingredients, warm service, and flavors that keep you coming back for more every weekend.',
                    'name' => 'Michael Chen',
                    'role' => 'Regular Guest',
                    'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=200&q=85',
                ],
                [
                    'id' => 3,
                    'quote' => 'We hosted a private dinner here and the catering team exceeded every expectation. Beautiful plating and outstanding hospitality throughout.',
                    'name' => 'Emily Carter',
                    'role' => 'Event Planner',
                    'avatar' => 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?auto=format&fit=crop&w=200&q=85',
                ],
            ]),
            'gallery_images' => json_encode([
                'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?auto=format&fit=crop&w=600&h=600&q=85',
                'https://images.unsplash.com/photo-1559339352-11d035aa65de?auto=format&fit=crop&w=600&h=600&q=85',
                'https://images.unsplash.com/photo-1550966871-3ed3cdb5ed0c?auto=format&fit=crop&w=600&h=600&q=85',
                'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=600&h=600&q=85',
                'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=600&h=600&q=85',
                'https://images.unsplash.com/photo-1478144592103-25e218a04891?auto=format&fit=crop&w=600&h=600&q=85',
            ]),
            'instagram_handle' => '@bynnasrestora',
            'social_facebook' => 'https://facebook.com',
            'social_instagram' => 'https://instagram.com',
            'social_twitter' => 'https://twitter.com',
            'social_youtube' => 'https://youtube.com',
        ];

        if (Schema::hasTable('site_settings')) {
            DB::table('site_settings')->update($defaults);
        }
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'logo_url', 'hero_bg_url', 'hero_image_url', 'hero_eyebrow', 'hero_headline',
                'hero_headline_accent', 'hero_description', 'popular_eyebrow', 'popular_title',
                'popular_subtitle', 'story_eyebrow', 'story_title', 'story_body', 'story_chef_url',
                'story_interior_url', 'story_food_url', 'story_years_label', 'story_chef_name',
                'story_chef_role', 'story_customers_label', 'story_points', 'hero_features',
                'value_props', 'testimonials', 'gallery_images', 'instagram_handle',
                'social_facebook', 'social_instagram', 'social_twitter', 'social_youtube',
            ]);
        });
    }
};
