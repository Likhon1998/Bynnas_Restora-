/** Live site settings injected from Laravel (Admin → Settings). */

const defaults = {
    restaurant_name: 'Bynnas Restora',
    tagline: 'Good Food, Great Mood',
    phone: '+8801711000099',
    email: 'info@bynnasrestora.com',
    address_line1: '123 Food Street',
    address_line2: 'Flavor Town',
    city: 'Dhaka',
    opening_hours: 'Mon – Sun: 10:00 AM – 11:00 PM',
    currency_symbol: '৳',
    reservations_enabled: true,
    online_ordering_enabled: true,
    vat_rate: 7,
    service_charge_rate: 5,
    footer_note: 'Delicious meals made with fresh ingredients and passion.',
    logo_url: null,
    hero_bg_url:
        'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=2200&q=85',
    hero_image_url:
        'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=1200&q=90',
    hero_eyebrow: 'Welcome to Fine Dining',
    hero_headline: 'Good Food,',
    hero_headline_accent: 'Great Moments',
    hero_description:
        'Delicious meals made with fresh ingredients and passion. Experience the perfect blend of taste and tradition.',
    popular_eyebrow: 'Our Specialties',
    popular_title: 'Popular Dishes',
    popular_subtitle:
        'Signature plates guests love most — crafted fresh daily with bold flavor and beautiful presentation.',
    story_eyebrow: 'Our Story',
    story_title: 'A Passion for Great Food',
    story_body:
        'Bynnas Restora began with a simple belief: great meals create lasting memories. From our kitchen to your table, we blend tradition with modern craft to deliver dishes that feel both familiar and exciting.\n\nEvery plate is prepared with care, every guest is welcomed like family, and every evening is designed around comfort, flavor, and hospitality.',
    story_chef_url:
        'https://images.unsplash.com/photo-1556910103-1c02745aae4d?auto=format&fit=crop&w=1000&q=85',
    story_interior_url:
        'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=800&q=85',
    story_food_url:
        'https://images.unsplash.com/photo-1513104890138-7c749659a591?auto=format&fit=crop&w=800&q=85',
    story_years_label: '15+',
    story_chef_name: 'Chef Bynnas',
    story_chef_role: 'Founder & Head Chef',
    story_customers_label: '25K+',
    story_points: [
        'Fresh ingredients sourced daily from trusted local partners',
        'Recipes crafted with passion and refined over 15 years',
        'Warm hospitality that makes every guest feel at home',
        'Consistent quality from kitchen to table, every service',
    ],
    hero_features: [
        { title: 'Fresh Ingredients', subtitle: 'Locally Sourced', key: 'leaf' },
        { title: 'Expert Chefs', subtitle: '10+ Years Experience', key: 'chef' },
        { title: 'Best Quality', subtitle: 'Premium Foods', key: 'award' },
        { title: 'Hygienic & Safe', subtitle: 'Highest Standards', key: 'shield' },
    ],
    value_props: [
        {
            title: 'Fast & Easy Booking',
            text: 'Reserve your table online in seconds with our simple booking flow.',
            key: 'calendar',
        },
        {
            title: 'Fresh & Healthy Food',
            text: 'Seasonal produce and carefully sourced ingredients in every dish.',
            key: 'salad',
        },
        {
            title: 'Cozy & Friendly Place',
            text: 'Warm ambiance and welcoming service for memorable evenings.',
            key: 'home',
        },
        {
            title: 'Hygienic & Safe',
            text: 'Strict kitchen standards so you can dine with complete confidence.',
            key: 'shield',
        },
    ],
    testimonials: [],
    gallery_images: [],
    instagram_handle: '@bynnasrestora',
    social_facebook: 'https://facebook.com',
    social_instagram: 'https://instagram.com',
    social_twitter: 'https://twitter.com',
    social_youtube: 'https://youtube.com',
    menu_hero_url:
        'https://images.unsplash.com/photo-1551183053-bf91a1d81141?auto=format&fit=crop&w=1800&q=85',
    menu_eyebrow: 'Delicious Choices, Made for You',
    menu_title: 'Our Menu',
    menu_subtitle:
        'Explore chef-crafted dishes made with fresh ingredients — from comfort classics to signature favorites, prepared with care every day.',
    menu_list_title: 'Our Delicious Menu',
    menu_allergen_title: 'Food Allergies?',
    menu_allergen_text: 'Tell us about any allergies and we’ll help you choose safely.',
    menu_fresh_title: 'Fresh Ingredients',
    menu_fresh_text: 'We source seasonal produce daily so every plate tastes vibrant and clean.',
    menu_fresh_image_url:
        'https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=800&q=85',
    menu_special_title:
        'Looking for something special? Our chefs are ready to create something amazing just for you.',
    menu_special_cta: 'Special Request',
    menu_special_image_url:
        'https://images.unsplash.com/photo-1600891964092-4316c288032e?auto=format&fit=crop&w=1600&q=85',
};

export function getSiteSettings() {
    if (typeof window !== 'undefined' && window.SITE_SETTINGS) {
        return { ...defaults, ...window.SITE_SETTINGS };
    }

    return { ...defaults };
}

export function formatMoney(amount, settings = getSiteSettings()) {
    const symbol = settings.currency_symbol || '৳';
    const value = Number(amount);
    if (Number.isNaN(value)) return `${symbol} —`;
    return `${symbol}${value.toLocaleString(undefined, { maximumFractionDigits: 0 })}`;
}

export function brandParts(settings = getSiteSettings()) {
    const name = (settings.restaurant_name || 'Bynnas Restora').trim();
    const parts = name.split(/\s+/);
    if (parts.length === 1) {
        return { script: parts[0], serif: '', tagline: settings.tagline || '' };
    }
    return {
        script: parts[0],
        serif: parts.slice(1).join(' '),
        tagline: settings.tagline || '',
    };
}

export function buildContactCards(settings = getSiteSettings()) {
    const address = [settings.address_line1, settings.address_line2, settings.city]
        .filter(Boolean)
        .join(', ');

    return [
        {
            key: 'location',
            title: 'Our Location',
            lines: address ? [address] : ['Address coming soon'],
        },
        {
            key: 'phone',
            title: 'Call Us',
            lines: [settings.phone || '—', settings.opening_hours || ''].filter(Boolean),
        },
        {
            key: 'email',
            title: 'Email Us',
            lines: [settings.email || '—', 'We reply within 24 hours'],
        },
        {
            key: 'hours',
            title: 'Opening Hours',
            lines: [settings.opening_hours || '—', 'Open All Days'],
        },
    ];
}
