import { useMemo, useState } from 'react';
import { Link } from 'react-router-dom';
import {
    ArrowLeft,
    ArrowRight,
    Lock,
    Mail,
    Search,
    Tag,
} from 'lucide-react';
import Navbar from '../components/home/Navbar';
import Footer from '../components/home/Footer';
import {
    GOLD,
    aboutSidebarImage,
    blogCategories,
    blogHeroImage,
    blogPosts,
    categoryCounts,
    popularPosts,
} from '../data/blogStatic';

const POSTS_PER_PAGE = 5;

export default function BlogPage() {
    const [activeCategory, setActiveCategory] = useState('All Posts');
    const [page, setPage] = useState(1);
    const [query, setQuery] = useState('');
    const [email, setEmail] = useState('');
    const [subscribed, setSubscribed] = useState(false);

    const filtered = useMemo(() => {
        let list = [...blogPosts];
        if (activeCategory !== 'All Posts') {
            list = list.filter((p) => p.category === activeCategory);
        }
        if (query.trim()) {
            const q = query.toLowerCase();
            list = list.filter(
                (p) =>
                    p.title.toLowerCase().includes(q) ||
                    p.excerpt.toLowerCase().includes(q) ||
                    p.category.toLowerCase().includes(q),
            );
        }
        return list;
    }, [activeCategory, query]);

    const totalPages = Math.max(1, Math.ceil(filtered.length / POSTS_PER_PAGE) || 1);
    // Show mock pagination like the design (up to 8) while keeping filter usable
    const displayTotal = Math.max(totalPages, filtered.length ? 8 : 1);
    const shown = filtered.slice(0, POSTS_PER_PAGE);

    const setCategory = (cat) => {
        setActiveCategory(cat);
        setPage(1);
    };

    return (
        <div className="min-h-screen bg-[#f7f4ef]">
            <Navbar />

            {/* Hero */}
            <section className="relative overflow-hidden bg-[#0d0d0d] text-white">
                <div className="absolute inset-0">
                    <img
                        src={blogHeroImage}
                        alt="Restaurant interior"
                        className="h-full w-full object-cover object-center"
                    />
                    <div className="absolute inset-0 bg-[#0d0d0d]/72" />
                    <div className="absolute inset-0 bg-gradient-to-r from-black/65 via-black/45 to-black/30" />
                </div>

                <div className="relative z-10 site-container py-28 lg:py-32">
                    <div className="mb-4 flex items-center gap-3">
                        <span className="h-px w-8" style={{ background: GOLD }} />
                        <span
                            className="text-[11px] font-semibold tracking-[0.22em] uppercase"
                            style={{ color: GOLD }}
                        >
                            Stories & News
                        </span>
                        <span className="h-px w-8" style={{ background: GOLD }} />
                    </div>

                    <h1 className="font-display max-w-3xl text-[2.6rem] leading-[1.1] font-semibold sm:text-5xl lg:text-[3.5rem]">
                        Our Blog{' '}
                        <span style={{ color: GOLD }}>Food for Thought</span>
                    </h1>

                    <p className="mt-4 max-w-xl text-[14px] leading-6 text-white/75 sm:text-[15px]">
                        Discover recipes, restaurant news, and food inspiration from the heart of
                        Bynnas Restora.
                    </p>
                </div>
            </section>

            {/* Feed + sidebar */}
            <section className="site-container py-10 lg:py-12">
                <div className="grid items-start gap-8 lg:grid-cols-[1.55fr_0.7fr] lg:gap-9">
                    {/* Posts column */}
                    <div>
                        <div className="mb-6 flex flex-wrap gap-2">
                            {blogCategories.map((cat) => {
                                const active = activeCategory === cat;
                                return (
                                    <button
                                        key={cat}
                                        type="button"
                                        onClick={() => setCategory(cat)}
                                        className={`rounded-full px-4 py-2 text-[13px] font-medium transition ${
                                            active
                                                ? 'text-white'
                                                : 'bg-transparent text-[#2b2620] hover:bg-[#c47a25]/10'
                                        }`}
                                        style={active ? { background: GOLD } : undefined}
                                    >
                                        {cat}
                                    </button>
                                );
                            })}
                        </div>

                        <div className="space-y-5">
                            {shown.length === 0 ? (
                                <div className="rounded-2xl border border-[#ebe4d8] bg-white px-6 py-12 text-center text-sm text-[#6b655c]">
                                    No posts found for this filter.
                                </div>
                            ) : (
                                shown.map((post) => (
                                    <article
                                        key={post.id}
                                        className="overflow-hidden rounded-2xl border border-[#ebe4d8] bg-white shadow-[0_10px_28px_rgba(13,13,13,0.04)] sm:grid sm:grid-cols-[240px_1fr]"
                                    >
                                        <img
                                            src={post.image}
                                            alt={post.title}
                                            className="h-48 w-full object-cover sm:h-full sm:min-h-[200px]"
                                            loading="lazy"
                                        />
                                        <div className="flex flex-col p-5 sm:p-6">
                                            <div className="flex items-center justify-between gap-3">
                                                <span
                                                    className="flex items-center gap-1.5 text-[11px] font-semibold tracking-[0.12em] uppercase"
                                                    style={{ color: GOLD }}
                                                >
                                                    <Tag className="h-3 w-3" />
                                                    {post.category}
                                                </span>
                                                <span className="text-[12px] text-[#8a847a]">
                                                    {post.date}
                                                </span>
                                            </div>

                                            <h2 className="font-display mt-2.5 text-[1.35rem] leading-snug font-semibold text-[#161616] sm:text-[1.5rem]">
                                                {post.title}
                                            </h2>

                                            <p className="mt-2.5 text-[13px] leading-6 text-[#6b655c]">
                                                {post.excerpt}
                                            </p>

                                            <div className="mt-auto flex items-center justify-between gap-3 pt-4">
                                                <div className="flex items-center gap-2.5">
                                                    <img
                                                        src={post.avatar}
                                                        alt={post.author}
                                                        className="h-8 w-8 rounded-full object-cover"
                                                    />
                                                    <span className="text-[13px] font-semibold text-[#2b2620]">
                                                        By {post.author}
                                                    </span>
                                                </div>
                                                <button
                                                    type="button"
                                                    className="inline-flex items-center gap-1.5 text-[13px] font-semibold transition hover:gap-2"
                                                    style={{ color: GOLD }}
                                                >
                                                    Read More
                                                    <ArrowRight className="h-3.5 w-3.5" />
                                                </button>
                                            </div>
                                        </div>
                                    </article>
                                ))
                            )}
                        </div>

                        {/* Pagination */}
                        <div className="mt-8 flex items-center justify-center gap-2">
                            <button
                                type="button"
                                aria-label="Previous page"
                                disabled={page <= 1}
                                onClick={() => setPage((p) => Math.max(1, p - 1))}
                                className="flex h-9 w-9 items-center justify-center rounded-md border border-[#e5ddd0] bg-white text-[#2b2620] disabled:opacity-35"
                            >
                                <ArrowLeft className="h-4 w-4" />
                            </button>

                            {[1, 2, 3].map((n) => (
                                <button
                                    key={n}
                                    type="button"
                                    onClick={() => setPage(n)}
                                    className={`flex h-9 w-9 items-center justify-center rounded-md text-sm font-semibold transition ${
                                        page === n
                                            ? 'text-white'
                                            : 'border border-[#e5ddd0] bg-white text-[#2b2620] hover:border-[#c47a25]'
                                    }`}
                                    style={page === n ? { background: GOLD } : undefined}
                                >
                                    {n}
                                </button>
                            ))}

                            <span className="px-1 text-sm text-[#8a847a]">…</span>

                            <button
                                type="button"
                                onClick={() => setPage(displayTotal)}
                                className={`flex h-9 w-9 items-center justify-center rounded-md text-sm font-semibold transition ${
                                    page === displayTotal
                                        ? 'text-white'
                                        : 'border border-[#e5ddd0] bg-white text-[#2b2620] hover:border-[#c47a25]'
                                }`}
                                style={page === displayTotal ? { background: GOLD } : undefined}
                            >
                                {displayTotal}
                            </button>

                            <button
                                type="button"
                                aria-label="Next page"
                                disabled={page >= displayTotal}
                                onClick={() => setPage((p) => Math.min(displayTotal, p + 1))}
                                className="flex h-9 w-9 items-center justify-center rounded-md border border-[#e5ddd0] bg-white text-[#2b2620] disabled:opacity-35"
                            >
                                <ArrowRight className="h-4 w-4" />
                            </button>
                        </div>
                    </div>

                    {/* Sidebar */}
                    <aside className="space-y-5">
                        <label className="relative block">
                            <span className="sr-only">Search blog</span>
                            <input
                                type="search"
                                value={query}
                                onChange={(e) => {
                                    setQuery(e.target.value);
                                    setPage(1);
                                }}
                                placeholder="Search blog…"
                                className="w-full rounded-xl border border-[#ebe4d8] bg-white py-3 pr-11 pl-4 text-sm text-[#2b2620] outline-none transition placeholder:text-[#9a948a] focus:border-[#c47a25]"
                            />
                            <Search className="pointer-events-none absolute top-1/2 right-3.5 h-4 w-4 -translate-y-1/2 text-[#9a948a]" />
                        </label>

                        <div className="rounded-2xl border border-[#ebe4d8] bg-white p-5 shadow-sm">
                            <h3 className="font-display text-xl font-semibold text-[#161616]">
                                About Bynnas Restora
                            </h3>
                            <div className="mt-2 h-0.5 w-12" style={{ background: GOLD }} />
                            <img
                                src={aboutSidebarImage}
                                alt="Bynnas Restora"
                                className="mt-4 h-36 w-full rounded-xl object-cover"
                                loading="lazy"
                            />
                            <p className="mt-3 text-[13px] leading-6 text-[#6b655c]">
                                A neighborhood destination for memorable dining — fresh ingredients,
                                warm hospitality, and flavors crafted with care.
                            </p>
                            <Link
                                to="/about"
                                className="mt-4 inline-flex w-full items-center justify-center rounded-[5px] border-2 px-4 py-2.5 text-sm font-semibold transition hover:bg-[#c47a25]/08"
                                style={{ borderColor: GOLD, color: GOLD }}
                            >
                                Learn More About Us
                            </Link>
                        </div>

                        <div className="rounded-2xl border border-[#ebe4d8] bg-white p-5 shadow-sm">
                            <h3 className="font-display text-xl font-semibold text-[#161616]">
                                Popular Posts
                            </h3>
                            <div className="mt-2 h-0.5 w-12" style={{ background: GOLD }} />
                            <ul className="mt-4 space-y-4">
                                {popularPosts.map((post) => (
                                    <li key={post.title} className="flex gap-3">
                                        <img
                                            src={post.image}
                                            alt=""
                                            className="h-14 w-14 shrink-0 rounded-lg object-cover"
                                            loading="lazy"
                                        />
                                        <div>
                                            <p className="text-[13px] leading-snug font-semibold text-[#2b2620]">
                                                {post.title}
                                            </p>
                                            <p className="mt-1 text-[11px] text-[#8a847a]">
                                                {post.date}
                                            </p>
                                        </div>
                                    </li>
                                ))}
                            </ul>
                        </div>

                        <div className="rounded-2xl border border-[#ebe4d8] bg-white p-5 shadow-sm">
                            <h3 className="font-display text-xl font-semibold text-[#161616]">
                                Categories
                            </h3>
                            <div className="mt-2 h-0.5 w-12" style={{ background: GOLD }} />
                            <ul className="mt-4 space-y-2.5">
                                {categoryCounts.map((cat) => (
                                    <li key={cat.name}>
                                        <button
                                            type="button"
                                            onClick={() => setCategory(cat.name)}
                                            className="flex w-full items-center justify-between gap-3 rounded-lg px-1 py-1.5 text-left text-[13px] text-[#3a342c] transition hover:text-[#c47a25]"
                                        >
                                            <span>{cat.name}</span>
                                            <span className="rounded-md border border-[#ebe4d8] bg-[#faf7f2] px-2 py-0.5 text-[11px] font-semibold text-[#6b655c]">
                                                {cat.count}
                                            </span>
                                        </button>
                                    </li>
                                ))}
                            </ul>
                        </div>

                        <div className="rounded-2xl bg-[#161616] p-5 text-white shadow-sm">
                            <div className="flex items-center gap-2">
                                <Mail className="h-4 w-4" style={{ color: GOLD }} />
                                <h3
                                    className="font-display text-xl font-semibold"
                                    style={{ color: GOLD }}
                                >
                                    Stay Updated
                                </h3>
                            </div>
                            <p className="mt-2 text-[13px] leading-6 text-white/70">
                                Get recipes, events, and restaurant news delivered to your inbox.
                            </p>

                            {subscribed ? (
                                <p className="mt-4 rounded-lg bg-white/10 px-3 py-3 text-sm text-white/85">
                                    You’re subscribed — thank you!
                                </p>
                            ) : (
                                <form
                                    className="mt-4 space-y-3"
                                    onSubmit={(e) => {
                                        e.preventDefault();
                                        if (email.trim()) setSubscribed(true);
                                    }}
                                >
                                    <input
                                        type="email"
                                        required
                                        value={email}
                                        onChange={(e) => setEmail(e.target.value)}
                                        placeholder="Enter your email"
                                        className="w-full rounded-lg border border-white/10 bg-white/5 px-3.5 py-2.5 text-sm text-white outline-none placeholder:text-white/40 focus:border-[#c47a25]"
                                    />
                                    <button
                                        type="submit"
                                        className="w-full rounded-[5px] py-2.5 text-sm font-semibold text-white transition hover:brightness-110"
                                        style={{ background: GOLD }}
                                    >
                                        Subscribe
                                    </button>
                                </form>
                            )}

                            <p className="mt-3 flex items-center gap-1.5 text-[11px] text-white/45">
                                <Lock className="h-3 w-3" />
                                We respect your privacy
                            </p>
                        </div>
                    </aside>
                </div>
            </section>

            <Footer />
        </div>
    );
}
