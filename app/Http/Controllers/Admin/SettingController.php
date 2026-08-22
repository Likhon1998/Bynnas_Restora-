<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Support\AdminNav;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.edit', [
            'user' => auth()->user(),
            'nav' => AdminNav::withActive('settings'),
            'icons' => AdminNav::icons(),
            'settings' => SiteSetting::current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        foreach ([
            'logo_url', 'hero_bg_url', 'hero_image_url', 'story_chef_url', 'story_interior_url',
            'story_food_url', 'social_facebook', 'social_instagram', 'social_twitter', 'social_youtube',
            'menu_hero_url', 'menu_fresh_image_url', 'menu_special_image_url',
        ] as $urlField) {
            if ($request->input($urlField) === '') {
                $request->merge([$urlField => null]);
            }
        }

        $data = $request->validate([
            'restaurant_name' => ['required', 'string', 'max:120'],
            'tagline' => ['nullable', 'string', 'max:160'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:160'],
            'address_line1' => ['nullable', 'string', 'max:160'],
            'address_line2' => ['nullable', 'string', 'max:160'],
            'city' => ['nullable', 'string', 'max:80'],
            'opening_hours' => ['nullable', 'string', 'max:160'],
            'currency_symbol' => ['required', 'string', 'max:8'],
            'timezone' => ['required', 'string', 'max:60'],
            'reservations_enabled' => ['nullable', 'boolean'],
            'online_ordering_enabled' => ['nullable', 'boolean'],
            'footer_note' => ['nullable', 'string'],
            'logo_url' => ['nullable', 'url', 'max:500'],
            'hero_bg_url' => ['nullable', 'url', 'max:500'],
            'hero_image_url' => ['nullable', 'url', 'max:500'],
            'hero_eyebrow' => ['nullable', 'string', 'max:120'],
            'hero_headline' => ['nullable', 'string', 'max:160'],
            'hero_headline_accent' => ['nullable', 'string', 'max:160'],
            'hero_description' => ['nullable', 'string'],
            'popular_eyebrow' => ['nullable', 'string', 'max:80'],
            'popular_title' => ['nullable', 'string', 'max:120'],
            'popular_subtitle' => ['nullable', 'string'],
            'story_eyebrow' => ['nullable', 'string', 'max:80'],
            'story_title' => ['nullable', 'string', 'max:160'],
            'story_body' => ['nullable', 'string'],
            'story_chef_url' => ['nullable', 'url', 'max:500'],
            'story_interior_url' => ['nullable', 'url', 'max:500'],
            'story_food_url' => ['nullable', 'url', 'max:500'],
            'story_years_label' => ['nullable', 'string', 'max:40'],
            'story_chef_name' => ['nullable', 'string', 'max:80'],
            'story_chef_role' => ['nullable', 'string', 'max:80'],
            'story_customers_label' => ['nullable', 'string', 'max:40'],
            'story_points_text' => ['nullable', 'string'],
            'gallery_images_text' => ['nullable', 'string'],
            'instagram_handle' => ['nullable', 'string', 'max:80'],
            'social_facebook' => ['nullable', 'url', 'max:500'],
            'social_instagram' => ['nullable', 'url', 'max:500'],
            'social_twitter' => ['nullable', 'url', 'max:500'],
            'social_youtube' => ['nullable', 'url', 'max:500'],
            'menu_hero_url' => ['nullable', 'url', 'max:500'],
            'menu_eyebrow' => ['nullable', 'string', 'max:120'],
            'menu_title' => ['nullable', 'string', 'max:160'],
            'menu_subtitle' => ['nullable', 'string'],
            'menu_list_title' => ['nullable', 'string', 'max:160'],
            'menu_allergen_title' => ['nullable', 'string', 'max:120'],
            'menu_allergen_text' => ['nullable', 'string'],
            'menu_fresh_title' => ['nullable', 'string', 'max:120'],
            'menu_fresh_text' => ['nullable', 'string'],
            'menu_fresh_image_url' => ['nullable', 'url', 'max:500'],
            'menu_special_title' => ['nullable', 'string', 'max:255'],
            'menu_special_cta' => ['nullable', 'string', 'max:80'],
            'menu_special_image_url' => ['nullable', 'url', 'max:500'],
        ]);

        $data['reservations_enabled'] = $request->boolean('reservations_enabled');
        $data['online_ordering_enabled'] = $request->boolean('online_ordering_enabled');

        $data['story_points'] = collect(preg_split('/\r\n|\r|\n/', (string) $request->input('story_points_text', '')))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();

        $data['gallery_images'] = collect(preg_split('/\r\n|\r|\n/', (string) $request->input('gallery_images_text', '')))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();

        unset($data['story_points_text'], $data['gallery_images_text']);

        // Keep structured JSON blocks unless we add full editors later
        $settings = SiteSetting::current();
        if (! $settings->hero_features) {
            $data['hero_features'] = SiteSetting::homepageDefaults()['hero_features'];
        }
        if (! $settings->value_props) {
            $data['value_props'] = SiteSetting::homepageDefaults()['value_props'];
        }
        if (! $settings->testimonials) {
            $data['testimonials'] = SiteSetting::homepageDefaults()['testimonials'];
        }

        $settings->update($data);

        return redirect()->route('admin.settings.edit')->with('success', 'Site settings saved. Public website will use these details.');
    }

    public function updatePayFirst(Request $request)
    {
        $payFirst = $request->boolean('pay_first');
        SiteSetting::current()->update(['pay_first' => $payFirst]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'pay_first' => $payFirst,
                'message' => $payFirst
                    ? 'Pay-first is on. Kitchen receives orders only after payment.'
                    : 'Pay-first is off. Dine-in can Send first, then Pay.',
            ]);
        }

        return redirect()->route('admin.settings.edit')->with('success', $payFirst ? 'Pay-first restaurant enabled.' : 'Pay-first restaurant disabled.');
    }
}
