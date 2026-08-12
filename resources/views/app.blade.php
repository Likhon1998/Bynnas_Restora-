<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        try {
            $site = \App\Models\SiteSetting::current();
        } catch (\Throwable) {
            $site = null;
        }
        $siteSettings = $site?->toPublicArray() ?? [
            'restaurant_name' => 'Bynnas Restora',
            'tagline' => 'Good Food, Great Mood',
            'phone' => '+8801711000099',
            'email' => 'info@bynnasrestora.com',
            'address_line1' => '123 Food Street',
            'address_line2' => 'Flavor Town',
            'city' => 'Dhaka',
            'opening_hours' => 'Mon – Sun: 10:00 AM – 11:00 PM',
            'currency_symbol' => '৳',
            'reservations_enabled' => true,
            'online_ordering_enabled' => true,
            'footer_note' => 'Delicious meals made with fresh ingredients and passion.',
        ];
        $siteName = $siteSettings['restaurant_name'] ?: 'Bynnas Restora';
        $siteTagline = $siteSettings['tagline'] ?: 'Good Food, Great Mood';
        $siteDesc = ($siteSettings['footer_note'] ?? null) ?: 'Delicious meals made with fresh ingredients and passion. Book a table or order online at Bynnas Restora.';
    @endphp

    <title>{{ $siteName }} — {{ $siteTagline }}</title>
    <meta name="description" content="{{ $siteDesc }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Great+Vibes&display=swap" rel="stylesheet">

    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
</head>
<body class="antialiased">
    <script>
        window.SITE_SETTINGS = @json($siteSettings);
    </script>
    <div id="app"></div>
</body>
</html>
