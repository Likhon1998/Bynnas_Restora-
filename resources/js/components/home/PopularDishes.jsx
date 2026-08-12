import { useEffect, useRef, useState } from 'react';
import { Link } from 'react-router-dom';
import { ArrowRight, ChefHat, ChevronLeft, ChevronRight, Heart, Star } from 'lucide-react';
import { useCart } from '../../context/CartContext';
import { formatMoney, getSiteSettings } from '../../data/siteSettings';
import Reveal from '../ui/Reveal';

const badgeStyles = {
    green: 'bg-emerald-600 text-white',
    red: 'bg-red-600 text-white',
    orange: 'bg-ember text-white',
    gold: 'bg-gold text-ink',
    blue: 'bg-sky-600 text-white',
};

export default function PopularDishes() {
    const settings = getSiteSettings();
    const { addItem, orderingEnabled } = useCart();
    const scrollerRef = useRef(null);
    const [dishes, setDishes] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        let cancelled = false;
        (async () => {
            try {
                const res = await fetch('/api/web/menu/featured');
                if (!res.ok) throw new Error('Failed to load featured menu');
                const data = await res.json();
                if (!cancelled) setDishes(Array.isArray(data.items) ? data.items : []);
            } catch {
                if (!cancelled) setDishes([]);
            } finally {
                if (!cancelled) setLoading(false);
            }
        })();
        return () => {
            cancelled = true;
        };
    }, []);

    const scrollBy = (dir) => scrollerRef.current?.scrollBy({ left: dir * 300, behavior: 'smooth' });

    return (
        <section id="menu" className="section-pad bg-paper">
            <div className="site-container">
                <div className="grid gap-6 lg:grid-cols-[230px_1fr] lg:items-start xl:gap-8">
                    <Reveal>
                        <div className="lg:sticky lg:top-24">
                            <p className="font-script text-[1.5rem] leading-none text-ember">
                                {settings.popular_eyebrow}
                            </p>
                            <h2 className="font-display mt-1.5 text-3xl font-semibold text-ink md:text-4xl">
                                {settings.popular_title}
                            </h2>
                            <p className="mt-3 text-[13px] leading-6 text-muted">
                                {settings.popular_subtitle}
                            </p>
                            <Link
                                to="/menu"
                                className="mt-5 inline-flex items-center gap-2 rounded-md border border-ink/15 bg-white px-4 py-2.5 text-sm font-semibold text-ink transition hover:border-ember hover:text-ember"
                            >
                                View Full Menu
                                <ArrowRight className="h-4 w-4" />
                            </Link>
                        </div>
                    </Reveal>

                    <div className="relative min-w-0">
                        <div className="absolute top-1/2 -right-1 z-10 hidden -translate-y-1/2 flex-col gap-2 md:flex">
                            <button
                                type="button"
                                onClick={() => scrollBy(-1)}
                                aria-label="Previous dishes"
                                className="flex h-10 w-10 items-center justify-center rounded-full bg-white text-ink shadow-md ring-1 ring-line hover:text-ember"
                            >
                                <ChevronLeft className="h-5 w-5" />
                            </button>
                            <button
                                type="button"
                                onClick={() => scrollBy(1)}
                                aria-label="Next dishes"
                                className="flex h-10 w-10 items-center justify-center rounded-full bg-ember text-white shadow-md hover:bg-ember-deep"
                            >
                                <ChevronRight className="h-5 w-5" />
                            </button>
                        </div>

                        <div
                            ref={scrollerRef}
                            className="no-scrollbar flex snap-x snap-mandatory gap-5 overflow-x-auto pb-2 xl:grid xl:grid-cols-4 xl:overflow-visible"
                        >
                            {loading && dishes.length === 0 ? (
                                <p className="text-sm text-muted">Loading popular dishes…</p>
                            ) : null}
                            {!loading && dishes.length === 0 ? (
                                <p className="text-sm text-muted">
                                    No featured dishes yet. Mark menu items as “Show on homepage” in
                                    admin.
                                </p>
                            ) : null}
                            {dishes.map((dish, i) => (
                                <Reveal
                                    key={dish.id}
                                    delay={70 + i * 60}
                                    className="min-w-[250px] snap-start xl:min-w-0"
                                >
                                    <article className="dish-card">
                                        <div className="media relative aspect-[4/3]">
                                            <img
                                                src={dish.image}
                                                alt={dish.name}
                                                className="h-full w-full object-cover"
                                                loading="lazy"
                                            />
                                            {dish.badge ? (
                                                <span
                                                    className={`absolute top-3 left-3 rounded-full px-2.5 py-1 text-[11px] font-semibold ${badgeStyles[dish.badgeTone] || badgeStyles.green}`}
                                                >
                                                    {dish.badge}
                                                </span>
                                            ) : null}
                                            <button
                                                type="button"
                                                aria-label={`Save ${dish.name}`}
                                                className="absolute top-3 right-3 flex h-8 w-8 items-center justify-center rounded-full bg-white/95 text-ink hover:text-ember"
                                            >
                                                <Heart className="h-4 w-4" />
                                            </button>
                                        </div>
                                        <div className="flex flex-1 flex-col p-4">
                                            <h3 className="font-display text-[1.28rem] font-semibold text-ink">
                                                {dish.name}
                                            </h3>
                                            <div className="mt-auto flex items-end justify-between gap-2 pt-3">
                                                <div>
                                                    <p className="font-semibold text-ember">
                                                        {formatMoney(dish.price, settings)}
                                                    </p>
                                                    <p className="mt-1 inline-flex items-center gap-1 text-xs text-muted">
                                                        <Star className="h-3.5 w-3.5 fill-gold text-gold" />
                                                        {dish.rating} ({dish.reviews})
                                                    </p>
                                                </div>
                                                <button
                                                    type="button"
                                                    disabled={!orderingEnabled}
                                                    aria-label={`Add ${dish.name} to cart`}
                                                    onClick={() => addItem(dish)}
                                                    className="flex h-10 w-10 items-center justify-center rounded-md bg-ember text-white hover:bg-ember-deep disabled:cursor-not-allowed disabled:opacity-50"
                                                >
                                                    <ChefHat className="h-4 w-4" />
                                                </button>
                                            </div>
                                        </div>
                                    </article>
                                </Reveal>
                            ))}
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}
