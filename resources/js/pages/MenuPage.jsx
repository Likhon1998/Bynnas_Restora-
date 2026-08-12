import { useEffect, useMemo, useState } from 'react';
import { Link } from 'react-router-dom';
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
import { useCart } from '../context/CartContext';
import { formatMoney, getSiteSettings } from '../data/siteSettings';

const badgeStyles = {
    green: 'bg-emerald-600 text-white',
    orange: 'bg-ember text-white',
    blue: 'bg-blue-600 text-white',
    red: 'bg-red-600 text-white',
    gold: 'bg-gold text-ink',
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

export default function MenuPage() {
    const settings = getSiteSettings();
    const {
        items: cart,
        addItem,
        changeQty,
        subtotal,
        service,
        tax,
        total,
        setOpen,
        orderingEnabled,
        vatRate,
        serviceRate,
    } = useCart();

    const [menuCategories, setMenuCategories] = useState([{ id: 'all', label: 'All Items', icon: 'grid' }]);
    const [menuItems, setMenuItems] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [category, setCategory] = useState('all');
    const [sort, setSort] = useState('popularity');
    const [visibleCount, setVisibleCount] = useState(9);
    const [liked, setLiked] = useState({});

    useEffect(() => {
        let cancelled = false;
        setLoading(true);
        setError('');
        fetch('/api/web/menu')
            .then((res) => {
                if (!res.ok) throw new Error('Could not load menu');
                return res.json();
            })
            .then((data) => {
                if (cancelled) return;
                setMenuCategories(data.categories || [{ id: 'all', label: 'All Items', icon: 'grid' }]);
                setMenuItems(data.items || []);
            })
            .catch(() => {
                if (cancelled) return;
                setMenuItems([]);
                setError('Menu is temporarily unavailable. Please try again shortly.');
            })
            .finally(() => {
                if (!cancelled) setLoading(false);
            });
        return () => {
            cancelled = true;
        };
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
    const activeCategoryLabel =
        menuCategories.find((c) => c.id === category)?.label || 'All Items';

    return (
        <div className="min-h-screen bg-paper">
            <Navbar />

            <section className="relative overflow-hidden bg-ink text-white">
                <div className="absolute inset-0">
                    <img
                        src={settings.menu_hero_url}
                        alt=""
                        aria-hidden="true"
                        className="h-full w-full object-cover object-center"
                    />
                    <div className="absolute inset-0 bg-gradient-to-r from-black/90 via-black/75 to-black/45" />
                </div>
                <div className="relative site-container pt-28 pb-14 lg:pt-32 lg:pb-16">
                    <p className="font-script text-[1.55rem] leading-none text-ember">
                        {settings.menu_eyebrow}
                    </p>
                    <h1 className="font-display mt-2 text-4xl font-semibold sm:text-5xl lg:text-[3.5rem]">
                        {settings.menu_title}
                    </h1>
                    <p className="mt-3 max-w-xl text-sm leading-6 text-white/75 sm:text-[15px]">
                        {settings.menu_subtitle}
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
                                {settings.menu_list_title}
                            </h2>
                            <p className="mt-1 text-sm text-muted">
                                {activeCategoryLabel}
                                {!loading ? ` · ${filtered.length} item${filtered.length === 1 ? '' : 's'}` : ''}
                            </p>
                            <span className="mt-2 block h-1 w-14 rounded-full bg-ember" />
                        </div>

                        {loading && (
                            <p className="text-sm text-muted">Loading menu from kitchen...</p>
                        )}

                        {!loading && error ? (
                            <p className="text-sm text-red-600">{error}</p>
                        ) : null}

                        {!loading && !error && shown.length === 0 && (
                            <p className="text-sm text-muted">
                                No menu items available in this category right now. Add dishes in
                                Admin → Menu Items.
                            </p>
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
                                            aria-label="Favorite"
                                            onClick={() =>
                                                setLiked((prev) => ({
                                                    ...prev,
                                                    [item.id]: !prev[item.id],
                                                }))
                                            }
                                            className="absolute top-3 right-3 flex h-8 w-8 items-center justify-center rounded-full bg-white/95 text-ink hover:text-ember"
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
                                                {formatMoney(item.price, settings)}
                                            </p>
                                            <button
                                                type="button"
                                                disabled={!orderingEnabled}
                                                onClick={() => addItem(item)}
                                                className="inline-flex items-center gap-1.5 rounded-md border border-ember px-3 py-2 text-[13px] font-semibold text-ember transition hover:bg-ember hover:text-white disabled:cursor-not-allowed disabled:opacity-50"
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
                                                {formatMoney(item.price, settings)}
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
                                    <span>{formatMoney(subtotal, settings)}</span>
                                </div>
                                <div className="flex justify-between text-muted">
                                    <span>Service {serviceRate}%</span>
                                    <span>{formatMoney(service, settings)}</span>
                                </div>
                                <div className="flex justify-between text-muted">
                                    <span>Tax {vatRate}%</span>
                                    <span>{formatMoney(tax, settings)}</span>
                                </div>
                                <div className="flex justify-between text-base font-semibold text-ink">
                                    <span>Total</span>
                                    <span>{formatMoney(total, settings)}</span>
                                </div>
                            </div>

                            <div className="mt-4 grid gap-2.5">
                                <button
                                    type="button"
                                    onClick={() => setOpen(true)}
                                    className="btn-primary w-full"
                                >
                                    View Cart
                                    <ArrowRight className="h-4 w-4" />
                                </button>
                                <button
                                    type="button"
                                    disabled={!orderingEnabled || cart.length === 0}
                                    onClick={() => setOpen(true)}
                                    className="inline-flex w-full items-center justify-center rounded-md border border-ember px-4 py-2.5 text-sm font-semibold text-ember transition hover:bg-ember hover:text-white disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    Checkout
                                </button>
                            </div>
                        </div>

                        <div className="rounded-xl border border-line bg-white p-5 shadow-sm">
                            <h3 className="font-display text-xl font-semibold text-ink">
                                {settings.menu_allergen_title}
                            </h3>
                            <p className="mt-2 text-[13px] leading-5 text-muted">
                                {settings.menu_allergen_text}
                            </p>
                            <Link
                                to="/contact"
                                className="mt-4 inline-flex items-center gap-2 rounded-md border border-ink/15 px-4 py-2 text-sm font-semibold text-ink transition hover:border-ember hover:text-ember"
                            >
                                View Allergen Info
                            </Link>
                        </div>

                        <div className="overflow-hidden rounded-xl border border-line bg-white shadow-sm">
                            <img
                                src={settings.menu_fresh_image_url}
                                alt={settings.menu_fresh_title}
                                className="aspect-[16/10] w-full object-cover"
                                loading="lazy"
                            />
                            <div className="p-5">
                                <h3 className="font-display text-xl font-semibold text-ink">
                                    {settings.menu_fresh_title}
                                </h3>
                                <p className="mt-2 text-[13px] leading-5 text-muted">
                                    {settings.menu_fresh_text}
                                </p>
                            </div>
                        </div>
                    </aside>
                </div>
            </section>

            <section className="site-container pb-8 lg:pb-10">
                <div className="relative overflow-hidden rounded-2xl">
                    <img
                        src={settings.menu_special_image_url}
                        alt=""
                        aria-hidden="true"
                        className="h-44 w-full object-cover sm:h-52"
                    />
                    <div className="absolute inset-0 bg-gradient-to-r from-black/85 via-black/70 to-black/40" />
                    <div className="absolute inset-0 flex flex-col justify-center gap-4 px-6 sm:flex-row sm:items-center sm:justify-between sm:px-10">
                        <p className="max-w-xl font-display text-2xl leading-snug font-semibold text-white sm:text-3xl">
                            {settings.menu_special_title}
                        </p>
                        <Link
                            to="/contact"
                            className="inline-flex shrink-0 items-center gap-2 rounded-md border border-white/50 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-white/10"
                        >
                            {settings.menu_special_cta}
                            <ArrowRight className="h-4 w-4" />
                        </Link>
                    </div>
                </div>
            </section>

            <Newsletter />
            <Footer />
        </div>
    );
}
