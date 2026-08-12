<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'restaurant_name', 'tagline', 'phone', 'email', 'address_line1', 'address_line2',
        'city', 'opening_hours', 'currency_symbol', 'timezone', 'reservations_enabled',
        'online_ordering_enabled', 'footer_note',
        'logo_url', 'hero_bg_url', 'hero_image_url', 'hero_eyebrow', 'hero_headline',
        'hero_headline_accent', 'hero_description',
        'popular_eyebrow', 'popular_title', 'popular_subtitle',
        'story_eyebrow', 'story_title', 'story_body', 'story_chef_url', 'story_interior_url',
        'story_food_url', 'story_years_label', 'story_chef_name', 'story_chef_role',
        'story_customers_label', 'story_points', 'hero_features', 'value_props',
        'testimonials', 'gallery_images', 'instagram_handle',
        'social_facebook', 'social_instagram', 'social_twitter', 'social_youtube',
        'menu_hero_url', 'menu_eyebrow', 'menu_title', 'menu_subtitle', 'menu_list_title',
        'menu_allergen_title', 'menu_allergen_text', 'menu_fresh_title', 'menu_fresh_text',
        'menu_fresh_image_url', 'menu_special_title', 'menu_special_cta', 'menu_special_image_url',
    ];

    protected function casts(): array
    {
        return [
            'reservations_enabled' => 'boolean',
            'online_ordering_enabled' => 'boolean',
            'story_points' => 'array',
            'hero_features' => 'array',
            'value_props' => 'array',
            'testimonials' => 'array',
            'gallery_images' => 'array',
        ];
    }

    public static function homepageDefaults(): array
    {
        return [
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
            'story_points' => [
                'Fresh ingredients sourced daily from trusted local partners',
                'Recipes crafted with passion and refined over 15 years',
                'Warm hospitality that makes every guest feel at home',
                'Consistent quality from kitchen to table, every service',
            ],
            'hero_features' => [
                ['title' => 'Fresh Ingredients', 'subtitle' => 'Locally Sourced', 'key' => 'leaf'],
                ['title' => 'Expert Chefs', 'subtitle' => '10+ Years Experience', 'key' => 'chef'],
                ['title' => 'Best Quality', 'subtitle' => 'Premium Foods', 'key' => 'award'],
                ['title' => 'Hygienic & Safe', 'subtitle' => 'Highest Standards', 'key' => 'shield'],
            ],
            'value_props' => [
                ['title' => 'Fast & Easy Booking', 'text' => 'Reserve your table online in seconds with our simple booking flow.', 'key' => 'calendar'],
                ['title' => 'Fresh & Healthy Food', 'text' => 'Seasonal produce and carefully sourced ingredients in every dish.', 'key' => 'salad'],
                ['title' => 'Cozy & Friendly Place', 'text' => 'Warm ambiance and welcoming service for memorable evenings.', 'key' => 'home'],
                ['title' => 'Hygienic & Safe', 'text' => 'Strict kitchen standards so you can dine with complete confidence.', 'key' => 'shield'],
            ],
            'testimonials' => [
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
            ],
            'gallery_images' => [
                'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?auto=format&fit=crop&w=600&h=600&q=85',
                'https://images.unsplash.com/photo-1559339352-11d035aa65de?auto=format&fit=crop&w=600&h=600&q=85',
                'https://images.unsplash.com/photo-1550966871-3ed3cdb5ed0c?auto=format&fit=crop&w=600&h=600&q=85',
                'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=600&h=600&q=85',
                'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=600&h=600&q=85',
                'https://images.unsplash.com/photo-1478144592103-25e218a04891?auto=format&fit=crop&w=600&h=600&q=85',
            ],
            'instagram_handle' => '@bynnasrestora',
            'social_facebook' => 'https://facebook.com',
            'social_instagram' => 'https://instagram.com',
            'social_twitter' => 'https://twitter.com',
            'social_youtube' => 'https://youtube.com',
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
    }

    public static function current(): self
    {
        $setting = static::query()->latest('id')->first();

        if ($setting) {
            return $setting;
        }

        return static::query()->create(array_merge([
            'restaurant_name' => 'Bynnas Restora',
            'tagline' => 'Good Food, Great Mood',
            'phone' => '+8801711000099',
            'email' => 'info@bynnasrestora.com',
            'address_line1' => '123 Food Street',
            'address_line2' => 'Flavor Town',
            'city' => 'Dhaka',
            'opening_hours' => 'Mon – Sun: 10:00 AM – 11:00 PM',
            'currency_symbol' => '৳',
            'timezone' => 'Asia/Dhaka',
            'reservations_enabled' => true,
            'online_ordering_enabled' => true,
            'footer_note' => 'Delicious meals made with fresh ingredients and passion.',
        ], static::homepageDefaults()));
    }

    public function toPublicArray(): array
    {
        $defaults = static::homepageDefaults();
        try {
            $tax = TaxSetting::current();
            $vatRate = (float) $tax->vat_rate;
            $serviceRate = (float) $tax->service_charge_rate;
        } catch (\Throwable) {
            $vatRate = 7.0;
            $serviceRate = 5.0;
        }

        return [
            'restaurant_name' => $this->restaurant_name,
            'tagline' => $this->tagline,
            'phone' => $this->phone,
            'email' => $this->email,
            'address_line1' => $this->address_line1,
            'address_line2' => $this->address_line2,
            'city' => $this->city,
            'opening_hours' => $this->opening_hours,
            'currency_symbol' => $this->currency_symbol,
            'reservations_enabled' => (bool) $this->reservations_enabled,
            'online_ordering_enabled' => (bool) $this->online_ordering_enabled,
            'vat_rate' => $vatRate,
            'service_charge_rate' => $serviceRate,
            'footer_note' => $this->footer_note,
            'logo_url' => $this->logo_url,
            'hero_bg_url' => $this->hero_bg_url ?: $defaults['hero_bg_url'],
            'hero_image_url' => $this->hero_image_url ?: $defaults['hero_image_url'],
            'hero_eyebrow' => $this->hero_eyebrow ?: $defaults['hero_eyebrow'],
            'hero_headline' => $this->hero_headline ?: $defaults['hero_headline'],
            'hero_headline_accent' => $this->hero_headline_accent ?: $defaults['hero_headline_accent'],
            'hero_description' => $this->hero_description ?: $defaults['hero_description'],
            'popular_eyebrow' => $this->popular_eyebrow ?: $defaults['popular_eyebrow'],
            'popular_title' => $this->popular_title ?: $defaults['popular_title'],
            'popular_subtitle' => $this->popular_subtitle ?: $defaults['popular_subtitle'],
            'story_eyebrow' => $this->story_eyebrow ?: $defaults['story_eyebrow'],
            'story_title' => $this->story_title ?: $defaults['story_title'],
            'story_body' => $this->story_body ?: $defaults['story_body'],
            'story_chef_url' => $this->story_chef_url ?: $defaults['story_chef_url'],
            'story_interior_url' => $this->story_interior_url ?: $defaults['story_interior_url'],
            'story_food_url' => $this->story_food_url ?: $defaults['story_food_url'],
            'story_years_label' => $this->story_years_label ?: $defaults['story_years_label'],
            'story_chef_name' => $this->story_chef_name ?: $defaults['story_chef_name'],
            'story_chef_role' => $this->story_chef_role ?: $defaults['story_chef_role'],
            'story_customers_label' => $this->story_customers_label ?: $defaults['story_customers_label'],
            'story_points' => $this->story_points ?: $defaults['story_points'],
            'hero_features' => $this->hero_features ?: $defaults['hero_features'],
            'value_props' => $this->value_props ?: $defaults['value_props'],
            'testimonials' => $this->testimonials ?: $defaults['testimonials'],
            'gallery_images' => $this->gallery_images ?: $defaults['gallery_images'],
            'instagram_handle' => $this->instagram_handle ?: $defaults['instagram_handle'],
            'social_facebook' => $this->social_facebook ?: $defaults['social_facebook'],
            'social_instagram' => $this->social_instagram ?: $defaults['social_instagram'],
            'social_twitter' => $this->social_twitter ?: $defaults['social_twitter'],
            'social_youtube' => $this->social_youtube ?: $defaults['social_youtube'],
            'menu_hero_url' => $this->menu_hero_url ?: $defaults['menu_hero_url'],
            'menu_eyebrow' => $this->menu_eyebrow ?: $defaults['menu_eyebrow'],
            'menu_title' => $this->menu_title ?: $defaults['menu_title'],
            'menu_subtitle' => $this->menu_subtitle ?: $defaults['menu_subtitle'],
            'menu_list_title' => $this->menu_list_title ?: $defaults['menu_list_title'],
            'menu_allergen_title' => $this->menu_allergen_title ?: $defaults['menu_allergen_title'],
            'menu_allergen_text' => $this->menu_allergen_text ?: $defaults['menu_allergen_text'],
            'menu_fresh_title' => $this->menu_fresh_title ?: $defaults['menu_fresh_title'],
            'menu_fresh_text' => $this->menu_fresh_text ?: $defaults['menu_fresh_text'],
            'menu_fresh_image_url' => $this->menu_fresh_image_url ?: $defaults['menu_fresh_image_url'],
            'menu_special_title' => $this->menu_special_title ?: $defaults['menu_special_title'],
            'menu_special_cta' => $this->menu_special_cta ?: $defaults['menu_special_cta'],
            'menu_special_image_url' => $this->menu_special_image_url ?: $defaults['menu_special_image_url'],
        ];
    }
}
