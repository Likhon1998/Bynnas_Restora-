/** Static visual content matched to the Bynnas Restora homepage mockup. */

export const images = {
    // Circular plate-style steak for the hero right panel
    hero: 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=1200&q=90',
    dishes: [
        {
            id: 1,
            name: 'Creamy Alfredo Pasta',
            price: '$12.99',
            rating: '4.9',
            reviews: '120',
            badge: 'Best Seller',
            badgeTone: 'green',
            image: 'https://images.unsplash.com/photo-1621996346565-e3dbc646d9a9?auto=format&fit=crop&w=900&q=85',
        },
        {
            id: 2,
            name: 'Grilled Chicken Steak',
            price: '$15.99',
            rating: '4.9',
            reviews: '98',
            badge: 'Hot',
            badgeTone: 'red',
            image: 'https://images.unsplash.com/photo-1532550907401-a500c9a57435?auto=format&fit=crop&w=900&q=85',
        },
        {
            id: 3,
            name: 'Bynnas Special Burger',
            price: '$11.99',
            rating: '4.7',
            reviews: '156',
            badge: 'Popular',
            badgeTone: 'orange',
            image: 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=900&q=85',
        },
        {
            id: 4,
            name: 'Margherita Pizza',
            price: '$13.99',
            rating: '4.6',
            reviews: '84',
            badge: 'Chef Pick',
            badgeTone: 'gold',
            image: 'https://images.unsplash.com/photo-1574071318508-1cdbab80d002?auto=format&fit=crop&w=900&q=85',
        },
    ],
    story: {
        chef: 'https://images.unsplash.com/photo-1556910103-1c02745aae4d?auto=format&fit=crop&w=1000&q=85',
        interior: 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=800&q=85',
        pizza: 'https://images.unsplash.com/photo-1513104890138-7c749659a591?auto=format&fit=crop&w=800&q=85',
    },
    testimonials: [
        {
            id: 1,
            quote: 'Absolutely incredible! The steak was perfectly cooked and the ambiance made our anniversary unforgettable. Bynnas Restora is our new favorite spot in the city.',
            name: 'Sarah Johnson',
            role: 'Food Blogger',
            avatar: 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=200&q=85',
        },
        {
            id: 2,
            quote: 'From booking to dessert, everything felt premium. Fresh ingredients, warm service, and flavors that keep you coming back for more every weekend.',
            name: 'Michael Chen',
            role: 'Regular Guest',
            avatar: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=200&q=85',
        },
        {
            id: 3,
            quote: 'We hosted a private dinner here and the catering team exceeded every expectation. Beautiful plating and outstanding hospitality throughout.',
            name: 'Emily Carter',
            role: 'Event Planner',
            avatar: 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?auto=format&fit=crop&w=200&q=85',
        },
    ],
    instagram: [
        'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?auto=format&fit=crop&w=600&h=600&q=85',
        'https://images.unsplash.com/photo-1559339352-11d035aa65de?auto=format&fit=crop&w=600&h=600&q=85',
        'https://images.unsplash.com/photo-1550966871-3ed3cdb5ed0c?auto=format&fit=crop&w=600&h=600&q=85',
        'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=600&h=600&q=85',
        'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=600&h=600&q=85',
        'https://images.unsplash.com/photo-1478144592103-25e218a04891?auto=format&fit=crop&w=600&h=600&q=85',
    ],
};

export const navLinks = [
    { label: 'Home', href: '#home', active: true },
    { label: 'Menu', href: '#menu' },
    { label: 'About Us', href: '#about' },
    { label: 'Reservations', href: '#reservations' },
    { label: 'Catering', href: '#catering' },
    { label: 'Blog', href: '#blog' },
    { label: 'Contact', href: '#contact' },
];

export const heroFeatures = [
    { title: 'Fresh Ingredients', subtitle: 'Locally Sourced', key: 'leaf' },
    { title: 'Expert Chefs', subtitle: '10+ Years Experience', key: 'chef' },
    { title: 'Best Quality', subtitle: 'Premium Foods', key: 'award' },
    { title: 'Hygienic & Safe', subtitle: 'Highest Standards', key: 'shield' },
];

export const valueProps = [
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
];

export const storyPoints = [
    'Fresh ingredients sourced daily from trusted local partners',
    'Recipes crafted with passion and refined over 15 years',
    'Warm hospitality that makes every guest feel at home',
    'Consistent quality from kitchen to table, every service',
];
