@extends('admin.layouts.app')

@section('title', 'Settings')

@section('content')
<div class="page-head">
    <div>
        <h1>Website & Branding</h1>
        <p>POS payment mode is at the top. The rest drives the public homepage, menu, and footer.</p>
    </div>
</div>

@if (session('success'))
    <div class="card" style="margin-bottom:12px;padding:12px 16px;border-color:#86efac;background:#f0fdf4">{{ session('success') }}</div>
@endif

<section class="card form-card" style="max-width:980px;margin-bottom:16px;border:1px solid #fdba74;background:#fff7ed">
    <h3 class="card-title" style="margin-bottom:8px">POS · Pay-first restaurant</h3>
    <p class="muted" style="margin:0 0 14px">Guests must pay before kitchen receives the order. Same switch is on the POS top bar.</p>
    <form method="POST" action="{{ route('admin.settings.pay-first') }}" id="payFirstForm">
        @csrf
        <input type="hidden" name="pay_first" value="0">
        <label class="pay-first-admin" style="display:inline-flex;align-items:center;gap:12px;cursor:pointer;background:#fff;border:1px solid #fdba74;border-radius:14px;padding:10px 14px">
            <span style="font-weight:800">Pay first</span>
            <span style="position:relative;width:48px;height:28px;flex-shrink:0">
                <input type="checkbox" name="pay_first" value="1" id="payFirstAdmin" @checked(old('pay_first', $settings->pay_first)) onchange="this.form.submit()" style="position:absolute;opacity:0;width:48px;height:28px;margin:0;cursor:pointer">
                <span style="display:block;width:48px;height:28px;border-radius:999px;background:{{ ($settings->pay_first ?? false) ? '#f28c28' : '#cbd5e1' }};position:relative">
                    <span style="position:absolute;top:3px;left:{{ ($settings->pay_first ?? false) ? '23px' : '3px' }};width:22px;height:22px;border-radius:999px;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.2)"></span>
                </span>
            </span>
            <strong style="color:{{ ($settings->pay_first ?? false) ? '#c2410c' : '#64748b' }}">{{ ($settings->pay_first ?? false) ? 'ON' : 'OFF' }}</strong>
        </label>
    </form>
</section>

<section class="card form-card" style="max-width:980px">
    <form method="POST" action="{{ route('admin.settings.update') }}">
        @csrf
        @method('PUT')

        <h3 class="card-title" style="margin-bottom:12px">Restaurant identity</h3>
        <div class="form-grid">
            <label>Restaurant name<input class="field" name="restaurant_name" value="{{ old('restaurant_name', $settings->restaurant_name) }}" required></label>
            <label>Tagline<input class="field" name="tagline" value="{{ old('tagline', $settings->tagline) }}"></label>
            <label class="span-2">Logo image URL (optional — leave blank to use text logo)<input class="field" name="logo_url" value="{{ old('logo_url', $settings->logo_url) }}" placeholder="https://..."></label>
            <label>Phone<input class="field" name="phone" value="{{ old('phone', $settings->phone) }}"></label>
            <label>Email<input class="field" type="email" name="email" value="{{ old('email', $settings->email) }}"></label>
            <label>Address line 1<input class="field" name="address_line1" value="{{ old('address_line1', $settings->address_line1) }}"></label>
            <label>Address line 2<input class="field" name="address_line2" value="{{ old('address_line2', $settings->address_line2) }}"></label>
            <label>City<input class="field" name="city" value="{{ old('city', $settings->city) }}"></label>
            <label>Opening hours<input class="field" name="opening_hours" value="{{ old('opening_hours', $settings->opening_hours) }}"></label>
            <label>Currency symbol<input class="field" name="currency_symbol" value="{{ old('currency_symbol', $settings->currency_symbol) }}" required></label>
            <label>Timezone<input class="field" name="timezone" value="{{ old('timezone', $settings->timezone) }}" required></label>
            <label style="display:flex;align-items:center;gap:8px;margin-top:28px">
                <input type="checkbox" name="reservations_enabled" value="1" @checked(old('reservations_enabled', $settings->reservations_enabled))>
                Reservations enabled on website
            </label>
            <label style="display:flex;align-items:center;gap:8px;margin-top:28px">
                <input type="checkbox" name="online_ordering_enabled" value="1" @checked(old('online_ordering_enabled', $settings->online_ordering_enabled))>
                Online ordering enabled
            </label>
            <label class="span-2">Footer note<textarea class="field" name="footer_note" rows="2">{{ old('footer_note', $settings->footer_note) }}</textarea></label>
        </div>

        <h3 class="card-title" style="margin:22px 0 12px">Hero section</h3>
        <div class="form-grid">
            <label class="span-2">Hero background image URL<input class="field" name="hero_bg_url" value="{{ old('hero_bg_url', $settings->hero_bg_url) }}"></label>
            <label class="span-2">Hero plate / dish image URL<input class="field" name="hero_image_url" value="{{ old('hero_image_url', $settings->hero_image_url) }}"></label>
            <label>Eyebrow<input class="field" name="hero_eyebrow" value="{{ old('hero_eyebrow', $settings->hero_eyebrow) }}"></label>
            <label>Headline (left)<input class="field" name="hero_headline" value="{{ old('hero_headline', $settings->hero_headline) }}"></label>
            <label>Headline accent (gold)<input class="field" name="hero_headline_accent" value="{{ old('hero_headline_accent', $settings->hero_headline_accent) }}"></label>
            <label class="span-2">Hero description<textarea class="field" name="hero_description" rows="3">{{ old('hero_description', $settings->hero_description) }}</textarea></label>
        </div>

        <h3 class="card-title" style="margin:22px 0 12px">Popular dishes section copy</h3>
        <p class="muted" style="margin:-4px 0 10px">Products themselves come from <strong>Menu Items</strong> with “Show on homepage” checked.</p>
        <div class="form-grid">
            <label>Eyebrow<input class="field" name="popular_eyebrow" value="{{ old('popular_eyebrow', $settings->popular_eyebrow) }}"></label>
            <label>Title<input class="field" name="popular_title" value="{{ old('popular_title', $settings->popular_title) }}"></label>
            <label class="span-2">Subtitle<textarea class="field" name="popular_subtitle" rows="2">{{ old('popular_subtitle', $settings->popular_subtitle) }}</textarea></label>
        </div>

        <h3 class="card-title" style="margin:22px 0 12px">Our Story</h3>
        <div class="form-grid">
            <label>Eyebrow<input class="field" name="story_eyebrow" value="{{ old('story_eyebrow', $settings->story_eyebrow) }}"></label>
            <label>Title<input class="field" name="story_title" value="{{ old('story_title', $settings->story_title) }}"></label>
            <label class="span-2">Story body (blank line = new paragraph)<textarea class="field" name="story_body" rows="5">{{ old('story_body', $settings->story_body) }}</textarea></label>
            <label class="span-2">Chef image URL<input class="field" name="story_chef_url" value="{{ old('story_chef_url', $settings->story_chef_url) }}"></label>
            <label>Interior image URL<input class="field" name="story_interior_url" value="{{ old('story_interior_url', $settings->story_interior_url) }}"></label>
            <label>Food image URL<input class="field" name="story_food_url" value="{{ old('story_food_url', $settings->story_food_url) }}"></label>
            <label>Years label<input class="field" name="story_years_label" value="{{ old('story_years_label', $settings->story_years_label) }}" placeholder="15+"></label>
            <label>Happy customers label<input class="field" name="story_customers_label" value="{{ old('story_customers_label', $settings->story_customers_label) }}" placeholder="25K+"></label>
            <label>Chef name<input class="field" name="story_chef_name" value="{{ old('story_chef_name', $settings->story_chef_name) }}"></label>
            <label>Chef role<input class="field" name="story_chef_role" value="{{ old('story_chef_role', $settings->story_chef_role) }}"></label>
            <label class="span-2">Story bullet points (one per line)<textarea class="field" name="story_points_text" rows="4">{{ old('story_points_text', implode("\n", $settings->story_points ?? [])) }}</textarea></label>
        </div>

        <h3 class="card-title" style="margin:22px 0 12px">Social & gallery</h3>
        <div class="form-grid">
            <label>Instagram handle<input class="field" name="instagram_handle" value="{{ old('instagram_handle', $settings->instagram_handle) }}" placeholder="@bynnasrestora"></label>
            <label>Facebook URL<input class="field" name="social_facebook" value="{{ old('social_facebook', $settings->social_facebook) }}"></label>
            <label>Instagram URL<input class="field" name="social_instagram" value="{{ old('social_instagram', $settings->social_instagram) }}"></label>
            <label>Twitter / X URL<input class="field" name="social_twitter" value="{{ old('social_twitter', $settings->social_twitter) }}"></label>
            <label>YouTube URL<input class="field" name="social_youtube" value="{{ old('social_youtube', $settings->social_youtube) }}"></label>
            <label class="span-2">Gallery image URLs (one per line)<textarea class="field" name="gallery_images_text" rows="6">{{ old('gallery_images_text', implode("\n", $settings->gallery_images ?? [])) }}</textarea></label>
        </div>

        <h3 class="card-title" style="margin:22px 0 12px">Menu page</h3>
        <p class="muted" style="margin:-4px 0 10px">Dishes &amp; categories come from <strong>Menu Items</strong>. Edit hero/banner copy and images here.</p>
        <div class="form-grid">
            <label class="span-2">Menu hero image URL<input class="field" name="menu_hero_url" value="{{ old('menu_hero_url', $settings->menu_hero_url) }}"></label>
            <label>Eyebrow<input class="field" name="menu_eyebrow" value="{{ old('menu_eyebrow', $settings->menu_eyebrow) }}"></label>
            <label>Page title<input class="field" name="menu_title" value="{{ old('menu_title', $settings->menu_title) }}"></label>
            <label class="span-2">Subtitle<textarea class="field" name="menu_subtitle" rows="2">{{ old('menu_subtitle', $settings->menu_subtitle) }}</textarea></label>
            <label class="span-2">List heading<input class="field" name="menu_list_title" value="{{ old('menu_list_title', $settings->menu_list_title) }}"></label>
            <label>Allergen card title<input class="field" name="menu_allergen_title" value="{{ old('menu_allergen_title', $settings->menu_allergen_title) }}"></label>
            <label>Allergen card text<input class="field" name="menu_allergen_text" value="{{ old('menu_allergen_text', $settings->menu_allergen_text) }}"></label>
            <label>Fresh card title<input class="field" name="menu_fresh_title" value="{{ old('menu_fresh_title', $settings->menu_fresh_title) }}"></label>
            <label>Fresh card text<input class="field" name="menu_fresh_text" value="{{ old('menu_fresh_text', $settings->menu_fresh_text) }}"></label>
            <label class="span-2">Fresh card image URL<input class="field" name="menu_fresh_image_url" value="{{ old('menu_fresh_image_url', $settings->menu_fresh_image_url) }}"></label>
            <label class="span-2">Special banner image URL<input class="field" name="menu_special_image_url" value="{{ old('menu_special_image_url', $settings->menu_special_image_url) }}"></label>
            <label class="span-2">Special banner text<textarea class="field" name="menu_special_title" rows="2">{{ old('menu_special_title', $settings->menu_special_title) }}</textarea></label>
            <label>Special CTA label<input class="field" name="menu_special_cta" value="{{ old('menu_special_cta', $settings->menu_special_cta) }}"></label>
        </div>

        <div class="form-actions"><button class="btn btn-gold" type="submit">Save Settings</button></div>
    </form>
</section>
@endsection
