/** Static visual content for the Menu page (matches mockup). */

export const menuCategories = [
    { id: 'all', label: 'All Items', icon: 'grid' },
    { id: 'appetizers', label: 'Appetizers', icon: 'appetizer' },
    { id: 'soups', label: 'Soups', icon: 'soup' },
    { id: 'mains', label: 'Main Course', icon: 'main' },
    { id: 'pasta', label: 'Pasta', icon: 'pasta' },
    { id: 'pizza', label: 'Pizza', icon: 'pizza' },
    { id: 'beverages', label: 'Beverages', icon: 'drink' },
    { id: 'desserts', label: 'Desserts', icon: 'dessert' },
];

export const menuItems = [
    {
        id: 1,
        name: 'Creamy Tomato Soup',
        description: 'Rich tomato bisque finished with cream and fresh basil.',
        price: 6.99,
        category: 'soups',
        badge: 'New',
        badgeTone: 'green',
        image: 'https://images.unsplash.com/photo-1547592166-23ac45744acd?auto=format&fit=crop&w=800&q=85',
    },
    {
        id: 2,
        name: 'Garlic Bread',
        description: 'Toasted artisan bread with garlic butter and herbs.',
        price: 4.49,
        category: 'appetizers',
        badge: null,
        badgeTone: null,
        image: 'https://images.unsplash.com/photo-1573140401552-3fab0b24306f?auto=format&fit=crop&w=800&q=85',
    },
    {
        id: 3,
        name: 'Caesar Salad',
        description: 'Crisp romaine, parmesan, croutons, and classic dressing.',
        price: 8.99,
        category: 'appetizers',
        badge: 'Popular',
        badgeTone: 'orange',
        image: 'https://images.unsplash.com/photo-1550304943-4f24f54ddde9?auto=format&fit=crop&w=800&q=85',
    },
    {
        id: 4,
        name: 'Creamy Alfredo Pasta',
        description: 'Fettuccine tossed in silky alfredo with cracked pepper.',
        price: 12.99,
        category: 'pasta',
        badge: 'Popular',
        badgeTone: 'orange',
        image: 'https://images.unsplash.com/photo-1621996346565-e3dbc646d9a9?auto=format&fit=crop&w=800&q=85',
    },
    {
        id: 5,
        name: 'Grilled Chicken Steak',
        description: 'Juicy grilled chicken with seasonal roasted vegetables.',
        price: 14.99,
        category: 'mains',
        badge: null,
        badgeTone: null,
        image: 'https://images.unsplash.com/photo-1532550907401-a500c9a57435?auto=format&fit=crop&w=800&q=85',
    },
    {
        id: 6,
        name: 'Bynnas Special Burger',
        description: 'House smash burger with cheddar, pickles, and secret sauce.',
        price: 11.99,
        category: 'mains',
        badge: 'New',
        badgeTone: 'green',
        image: 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=800&q=85',
    },
    {
        id: 7,
        name: 'Margherita Pizza',
        description: 'Wood-fired pizza with tomato, mozzarella, and fresh basil.',
        price: 13.99,
        category: 'pizza',
        badge: null,
        badgeTone: null,
        image: 'https://images.unsplash.com/photo-1574071318508-1cdbab80d002?auto=format&fit=crop&w=800&q=85',
    },
    {
        id: 8,
        name: 'Penne Arrabbiata',
        description: 'Spicy tomato sauce with garlic, chili, and penne pasta.',
        price: 11.49,
        category: 'pasta',
        badge: 'Popular',
        badgeTone: 'orange',
        image: 'https://images.unsplash.com/photo-1563379926898-05f4575a45d8?auto=format&fit=crop&w=800&q=85',
    },
    {
        id: 9,
        name: 'Grilled Salmon',
        description: 'Atlantic salmon with lemon butter and garden greens.',
        price: 16.99,
        category: 'mains',
        badge: 'New',
        badgeTone: 'green',
        image: 'https://images.unsplash.com/photo-1467003909585-2f8a72700288?auto=format&fit=crop&w=800&q=85',
    },
];

export const initialCart = [
    { id: 5, name: 'Grilled Chicken Steak', price: 14.99, qty: 1, image: menuItems[4].image },
    { id: 4, name: 'Creamy Alfredo Pasta', price: 12.99, qty: 1, image: menuItems[3].image },
    { id: 2, name: 'Garlic Bread', price: 4.49, qty: 1, image: menuItems[1].image },
];

export const menuHeroImage =
    'https://images.unsplash.com/photo-1551183053-bf91a1d81141?auto=format&fit=crop&w=1800&q=85';

export const freshIngredientsImage =
    'https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=800&q=85';

export const specialBannerImage =
    'https://images.unsplash.com/photo-1600891964092-4316c288032e?auto=format&fit=crop&w=1600&q=85';
