import { useEffect, useMemo, useState } from 'react';
import {
    ArrowRight,
    ChevronDown,
    Coffee,
    Cookie,
    Grid2x2,
    Heart,
    LayoutGrid,
    Minus,
    Plus,
    Salad,
    Soup,
    UtensilsCrossed,
} from 'lucide-react';
import Navbar from '../components/home/Navbar';
import Newsletter from '../components/home/Newsletter';
import Footer from '../components/home/Footer';
import {
    freshIngredientsImage,
    menuHeroImage,
    specialBannerImage,
} from '../data/menuStatic';

const badgeStyles = {
    green: 'bg-emerald-600 text-white',
    orange: 'bg-ember text-white',
    blue: 'bg-blue-600 text-white',
    red: 'bg-red-600 text-white',
};

const categoryIcons = {
    grid: LayoutGrid,
    appetizer: Salad,
    soup: Soup,
    main: UtensilsCrossed,
    pasta: UtensilsCrossed,
    pizza: Grid2x2,
    burger: UtensilsCrossed,
    drink: Coffee,
    dessert: Cookie,
};

function money(n) {
    return `৳ ${Number(n).toLocaleString('en-BD', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

export default function MenuPage() {
    const [menuCategories, setMenuCategories] = useState([{ id: 'all', label: 'All Items', icon: 'grid' }]);
    const [menuItems, setMenuItems] = useState([]);
    const [loading, setLoading] = useState(true);
    const [category, setCategory] = useState('all');
    const [sort, setSort] = useState('popularity');
    const [visibleCount, setVisibleCount] = useState(9);
    const [cart, setCart] = useState([]);
    const [liked, setLiked] = useState({});

    useEffect(() => {
        fetch('/api/web/menu')
            .then((res) => res.json())
            .then((data) => {
                setMenuCategories(data.categories || []);
                setMenuItems(
                    (data.items || []).map((item) => ({
                        ...item,
                        image: item.image,
                        isFavorite: item.isFavorite,
                    })),
                );
            })
            .catch(() => setMenuItems([]))
            .finally(() => setLoading(false));
    }, []);

    const filtered = useMemo(() => {
        let list =
            category === 'all'
                ? [...menuItems]
                : menuItems.filter((i) => i.category === category);

        if (sort === 'price-asc') list.sort((a, b) => a.price - b.price);
        if (sort === 'price-desc') list.sort((a, b) => b.price - a.price);
        if (sort === 'name') list.sort((a, b) => a.name.localeCompare(b.name));
        if (sort === 'popularity') list.sort((a, b) => Number(b.isPopular) - Number(a.isPopular));
        return list;
    }, [category, sort, menuItems]);

    const shown = filtered.slice(0, visibleCount);

    const subtotal = cart.reduce((sum, item) => sum + item.price * item.qty, 0);
    const tax = subtotal * 0.07;
    const service = subtotal * 0.05;
    const total = subtotal + tax + service;
    const cartCount = cart.reduce((sum, item) => sum + item.qty, 0);

    const addToCart = (item) => {
        setCart((prev) => {
            const found = prev.find((p) => p.id === item.id);
            if (found) {
                return prev.map((p) => (p.id === item.id ? { ...p, qty: p.qty + 1 } : p));
            }
            return [
                ...prev,
                { id: item.id, name: item.name, price: item.price, qty: 1, image: item.image },
            ];
        });
    };

    const changeQty = (id, delta) => {
        setCart((prev) =>
            prev
                .map((p) => (p.id === id ? { ...p, qty: Math.max(0, p.qty + delta) } : p))
                .filter((p) => p.qty > 0),
        );
    };

    return (
        <div className="min-h-screen bg-paper">
            <Navbar cartCount={cartCount} />

            <section className="relative overflow-hidden bg-ink text-white">
                <div className="absolute inset-0">
                    <img
                        src={menuHeroImage}
                        alt="Pasta dish"
                        className="h-full w-full object-cover object-center"
                    />
                    <div className="absolute inset-0 bg-gradient-to-r from-black/90 via-black/75 to-black/45" />
                </div>
                <div className="relative site-container pt-28 pb-14 lg:pt-32 lg:pb-16">
                    <p className="font-script text-[1.55rem] leading-none text-ember">
                        Delicious Choices, Made for You
                    </p>
                    <h1 className="font-display mt-2 text-4xl font-semibold sm:text-5xl lg:text-[3.5rem]">
                        Our Menu
                    </h1>
                    <p className="mt-3 max-w-xl text-sm leading-6 text-white/75 sm:text-[15px]">
                        Explore chef-crafted dishes made with fresh ingredients — from comfort
                        classics to signature favorites, prepared with care every day.
                    </p>
                </div>
            </section>

            <section className="border-b border-line bg-white">
                <div className="site-container flex flex-col gap-4 py-4 lg:flex-row lg:items-center lg:justify-between">
                    <div className="no-scrollbar flex gap-2 overflow-x-auto pb-1">
                        {menuCategories.map((cat) => {
                            const Icon = categoryIcons[cat.icon] || LayoutGrid;
                            const active = category === cat.id;
                            return (
                                <button
                                    key={cat.id}
                                    type="button"
                                    onClick={() => {
                                        setCategory(cat.id);
                                        setVisibleCount(9);
                                    }}
                                    className={`inline-flex shrink-0 items-center gap-2 rounded-full px-3.5 py-2 text-[13px] font-medium transition ${
                                        active
                                            ? 'bg-ember text-white shadow-md shadow-ember/25'
                                            : 'bg-cream text-ink hover:bg-cream-2'
                                    }`}
                                >
                                    <Icon className="h-3.5 w-3.5" />
                                    {cat.label}
                                </button>
                            );
                        })}
                    </div>
                    <label className="flex items-center gap-2 text-sm text-muted">
                        <span className="whitespace-nowrap">Sort by:</span>
                        <select
                            value={sort}
                            onChange={(e) => setSort(e.target.value)}
                            className="rounded-md border border-line bg-white px-3 py-2 text-sm text-ink outline-none focus:border-ember"
                        >
                            <option value="popularity">Popularity</option>
                            <option value="price-asc">Price: Low to High</option>
                            <option value="price-desc">Price: High to Low</option>
                            <option value="name">Name</option>
                        </select>
                    </label>
                </div>
            </section>

            <section className="site-container py-8 lg:py-10">
                <div className="grid gap-7 lg:grid-cols-[1fr_300px] xl:grid-cols-[1fr_320px] xl:gap-8">
                    <div>
                        <div className="mb-5">
                            <h2 className="font-display text-3xl font-semibold text-ink">
                                Our Delicious Menu
                            </h2>
                            <span className="mt-2 block h-1 w-14 rounded-full bg-ember" />
                        </div>

                        {loading && (
                            <p className="text-sm text-muted">Loading menu from kitchen...</p>
                        )}

                        {!loading && shown.length === 0 && (
                            <p className="text-sm text-muted">No menu items available right now.</p>
                        )}

                        <div className="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                            {shown.map((item) => (
                                <article
                                    key={item.id}
                                    className="overflow-hidden rounded-xl border border-line bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-md"
                                >
                                    <div className="relative aspect-[4/3] overflow-hidden">
                                        <img
                                            src={item.image}
                                            alt={item.name}
                                            className="h-full w-full object-cover"
                                            loading="lazy"
                                        />
                                        {item.badge && (
                                            <span
                                                className={`absolute top-3 left-3 rounded-full px-2.5 py-1 text-[11px] font-semibold ${badgeStyles[item.badgeTone] || badgeStyles.orange}`}
                                            >
                                                {item.badge}
                                            </span>
                                        )}
                                        <button
                                            type="button"
                                            aria-label={`Save ${item.name}`}
                                            onClick={() =>
                                                setLiked((prev) => ({
                                                    ...prev,
                                                    [item.id]: !prev[item.id],
                                                }))
                                            }
                                            className="absolute top-3 right-3 flex h-8 w-8 items-center justify-center rounded-full bg-white/95 text-ink transition hover:text-ember"
                                        >
                                            <Heart
                                                className={`h-4 w-4 ${liked[item.id] || item.isFavorite ? 'fill-ember text-ember' : ''}`}
                                            />
                                        </button>
                                    </div>
                                    <div className="p-4">
                                        <h3 className="font-display text-xl font-semibold text-ink">
                                            {item.name}
                                        </h3>
                                        <p className="mt-1 line-clamp-2 text-[13px] leading-5 text-muted">
                                            {item.description}
                                        </p>
                                        <div className="mt-4 flex items-center justify-between gap-3">
                                            <p className="text-base font-semibold text-ink">
                                                {money(item.price)}
                                            </p>
                                            <button
                                                type="button"
                                                onClick={() => addToCart(item)}
                                                className="inline-flex items-center gap-1.5 rounded-md border border-ember px-3 py-2 text-[13px] font-semibold text-ember transition hover:bg-ember hover:text-white"
                                            >
                                                <Plus className="h-3.5 w-3.5" />
                                                Add to Cart
                                            </button>
                                        </div>
                                    </div>
                                </article>
                            ))}
                        </div>

                        {visibleCount < filtered.length && (
                            <div className="mt-8 flex justify-center">
                                <button
                                    type="button"
                                    onClick={() => setVisibleCount((n) => n + 6)}
                                    className="inline-flex items-center gap-2 rounded-md border border-ink/15 bg-white px-5 py-2.5 text-sm font-semibold text-ink transition hover:border-ember hover:text-ember"
                                >
                                    Load More
                                    <ChevronDown className="h-4 w-4" />
                                </button>
                            </div>
                        )}
                    </div>

                    <aside className="space-y-5 lg:sticky lg:top-24 lg:self-start">
                        <div className="rounded-xl border border-line bg-white p-5 shadow-sm">
                            <h3 className="font-display text-2xl font-semibold text-ink">
                                Your Order
                            </h3>
                            <div className="mt-4 space-y-4">
                                {cart.length === 0 && (
                                    <p className="text-sm text-muted">Your cart is empty.</p>
                                )}
                                {cart.map((item) => (
                                    <div key={item.id} className="flex items-center gap-3">
                                        <img
                                            src={item.image}
                                            alt={item.name}
                                            className="h-14 w-14 rounded-lg object-cover"
                                        />
                                        <div className="min-w-0 flex-1">
                                            <p className="truncate text-sm font-semibold text-ink">
                                                {item.name}
                                            </p>
                                            <p className="text-sm text-ember">
                                                {money(item.price)}
                                            </p>
                                        </div>
                                        <div className="flex items-center gap-1.5 rounded-md border border-line px-1.5 py-1">
                                            <button
                                                type="button"
                                                aria-label="Decrease"
                                                onClick={() => changeQty(item.id, -1)}
                                                className="rounded p-1 hover:bg-cream"
                                            >
                                                <Minus className="h-3.5 w-3.5" />
                                            </button>
                                            <span className="w-5 text-center text-sm font-semibold">
                                                {item.qty}
                                            </span>
                                            <button
                                                type="button"
                                                aria-label="Increase"
                                                onClick={() => changeQty(item.id, 1)}
                                                className="rounded p-1 hover:bg-cream"
                                            >
                                                <Plus className="h-3.5 w-3.5" />
                                            </button>
                                        </div>
                                    </div>
                                ))}
                            </div>

                            <div className="mt-5 space-y-1.5 border-t border-line pt-4 text-sm">
                                <div className="flex justify-between text-muted">
                                    <span>Subtotal</span>
                                    <span>{money(subtotal)}</span>
                                </div>
                                <div className="flex justify-between text-muted">
                                    <span>Service 5%</span>
                                    <span>{money(service)}</span>
                                </div>
                                <div className="flex justify-between text-muted">
                                    <span>Tax 7%</span>
                                    <span>{money(tax)}</span>
                                </div>
                                <div className="flex justify-between text-base font-semibold text-ink">
                                    <span>Total</span>
                                    <span>{money(total)}</span>
                                </div>
                            </div>

                            <div className="mt-4 grid gap-2.5">
                                <button type="button" className="btn-primary w-full">
                                    View Cart
                                    <ArrowRight className="h-4 w-4" />
                                </button>
                                <button
                                    type="button"
                                    className="inline-flex w-full items-center justify-center rounded-md border border-ember px-4 py-2.5 text-sm font-semibold text-ember transition hover:bg-ember hover:text-white"
                                >
                                    Checkout
                                </button>
                            </div>
                        </div>

                        <div className="rounded-xl border border-line bg-white p-5 shadow-sm">
                            <h3 className="font-display text-xl font-semibold text-ink">
                                Food Allergies?
                            </h3>
                            <p className="mt-2 text-[13px] leading-5 text-muted">
                                Tell us about any allergies and we’ll help you choose safely.
                            </p>
                            <button
                                type="button"
                                className="mt-4 inline-flex items-center gap-2 rounded-md border border-ink/15 px-4 py-2 text-sm font-semibold text-ink transition hover:border-ember hover:text-ember"
                            >
                                View Allergen Info
                            </button>
                        </div>

                        <div className="overflow-hidden rounded-xl border border-line bg-white shadow-sm">
                            <img
                                src={freshIngredientsImage}
                                alt="Fresh ingredients"
                                className="aspect-[16/10] w-full object-cover"
                                loading="lazy"
                            />
                            <div className="p-5">
                                <h3 className="font-display text-xl font-semibold text-ink">
                                    Fresh Ingredients
                                </h3>
                                <p className="mt-2 text-[13px] leading-5 text-muted">
                                    We source seasonal produce daily so every plate tastes vibrant
                                    and clean.
                                </p>
                            </div>
                        </div>
                    </aside>
                </div>
            </section>

            <section className="site-container pb-8 lg:pb-10">
                <div className="relative overflow-hidden rounded-2xl">
                    <img
                        src={specialBannerImage}
                        alt="Chef special"
                        className="h-44 w-full object-cover sm:h-52"
                    />
                    <div className="absolute inset-0 bg-gradient-to-r from-black/85 via-black/70 to-black/40" />
                    <div className="absolute inset-0 flex flex-col justify-center gap-4 px-6 sm:flex-row sm:items-center sm:justify-between sm:px-10">
                        <p className="max-w-xl font-display text-2xl leading-snug font-semibold text-white sm:text-3xl">
                            Looking for something special? Our chefs are ready to create something
                            amazing just for you.
                        </p>
                        <button
                            type="button"
                            className="inline-flex shrink-0 items-center gap-2 rounded-md border border-white/50 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-white/10"
                        >
                            Special Request
                            <ArrowRight className="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </section>

            <Newsletter />
            <Footer />
        </div>
    );
}
