import { useEffect, useRef, useState } from 'react';
import { Link } from 'react-router-dom';
import {
    ArrowLeft,
    ArrowRight,
    Briefcase,
    Cake,
    Check,
    ChefHat,
    Eye,
    FileText,
    Gift,
    Heart,
    Leaf,
    Medal,
    Phone,
    Send,
    Sparkles,
    Tag,
    Truck,
    Users,
    UtensilsCrossed,
} from 'lucide-react';
import Navbar from '../components/home/Navbar';
import Footer from '../components/home/Footer';
import {
    GOLD,
    cateringHeroImage,
    ctaDishImage,
    galleryImages,
    heroFeatures,
    occasionCards,
    occasionList,
    packages,
    whyChoose,
} from '../data/cateringStatic';

const heroIcons = {
    menu: UtensilsCrossed,
    fresh: Leaf,
    team: Users,
    truck: Truck,
};

const occasionIcons = {
    heart: Heart,
    briefcase: Briefcase,
    cake: Cake,
    users: Users,
    sparkles: Sparkles,
    gift: Gift,
};

const whyIcons = {
    chef: ChefHat,
    leaf: Leaf,
    medal: Medal,
    plane: Send,
    tag: Tag,
};

function SparkLabel({ children, light = false }) {
    return (
        <div className="mb-3 flex items-center gap-2">
            <Sparkles
                className="h-3.5 w-3.5"
                style={{ color: GOLD }}
                fill={GOLD}
            />
            <span
                className={`text-[11px] font-semibold tracking-[0.18em] uppercase ${
                    light ? 'text-white/90' : ''
                }`}
                style={{ color: light ? undefined : GOLD }}
            >
                {children}
            </span>
            <Sparkles
                className="h-3.5 w-3.5"
                style={{ color: GOLD }}
                fill={GOLD}
            />
        </div>
    );
}

export default function CateringPage() {
    const galleryRef = useRef(null);
    const [canPrev, setCanPrev] = useState(false);
    const [canNext, setCanNext] = useState(true);

    const syncGallery = () => {
        const el = galleryRef.current;
        if (!el) return;
        setCanPrev(el.scrollLeft > 8);
        setCanNext(el.scrollLeft + el.clientWidth < el.scrollWidth - 8);
    };

    useEffect(() => {
        const el = galleryRef.current;
        if (!el) return;
        syncGallery();
        el.addEventListener('scroll', syncGallery, { passive: true });
        window.addEventListener('resize', syncGallery);
        return () => {
            el.removeEventListener('scroll', syncGallery);
            window.removeEventListener('resize', syncGallery);
        };
    }, []);

    const scrollGallery = (dir) => {
        const el = galleryRef.current;
        if (!el) return;
        el.scrollBy({ left: dir * (el.clientWidth * 0.72), behavior: 'smooth' });
    };

    const scrollToQuote = () => {
        document.getElementById('packages')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    };

    return (
        <div className="min-h-screen bg-[#faf7f2]">
            <Navbar />

            {/* Hero */}
            <section className="relative overflow-hidden bg-[#0d0d0d] text-white">
                <div className="absolute inset-0">
                    <img
                        src={cateringHeroImage}
                        alt="Catered banquet table"
                        className="h-full w-full object-cover object-center"
                    />
                    <div className="absolute inset-0 bg-[#0d0d0d]/68" />
                    <div className="absolute inset-0 bg-gradient-to-r from-black/70 via-black/45 to-black/25" />
                </div>

                <div className="relative z-10 site-container pt-28 pb-14 lg:pt-32 lg:pb-16">
                    <SparkLabel light>
                        <span style={{ color: GOLD }}>Delicious Food for Every Occasion</span>
                    </SparkLabel>

                    <h1 className="font-display max-w-3xl text-[2.6rem] leading-[1.08] font-semibold sm:text-5xl lg:text-[3.6rem]">
                        Catering Made{' '}
                        <span style={{ color: GOLD }}>Memorable</span>
                    </h1>

                    <p className="mt-4 max-w-xl text-[14px] leading-6 text-white/78 sm:text-[15px] sm:leading-7">
                        From intimate gatherings to grand celebrations, our professional catering
                        brings exceptional flavor, seamless service, and unforgettable experiences
                        to your table.
                    </p>

                    <div className="mt-7 flex flex-wrap gap-3">
                        <button
                            type="button"
                            onClick={scrollToQuote}
                            className="inline-flex items-center gap-2 rounded-[5px] px-5 py-3 text-sm font-semibold text-white transition hover:brightness-110"
                            style={{ background: GOLD }}
                        >
                            <FileText className="h-4 w-4" />
                            Request a Quote
                        </button>
                        <Link
                            to="/menu"
                            className="inline-flex items-center gap-2 rounded-[5px] border-2 bg-transparent px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/5"
                            style={{ borderColor: GOLD }}
                        >
                            <Eye className="h-4 w-4" style={{ color: GOLD }} />
                            View Catering Menu
                        </Link>
                    </div>

                    <div className="mt-12 grid gap-5 border-t border-white/10 pt-8 sm:grid-cols-2 lg:grid-cols-4 lg:gap-6">
                        {heroFeatures.map((item) => {
                            const Icon = heroIcons[item.key];
                            return (
                                <div key={item.title} className="flex items-start gap-3">
                                    <span
                                        className="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-full"
                                        style={{ background: `${GOLD}22`, color: GOLD }}
                                    >
                                        <Icon className="h-5 w-5" strokeWidth={1.7} />
                                    </span>
                                    <div>
                                        <p className="text-[14px] font-semibold text-white">
                                            {item.title}
                                        </p>
                                        <p className="mt-0.5 text-[12px] leading-5 text-white/60">
                                            {item.text}
                                        </p>
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                </div>
            </section>

            {/* Occasions */}
            <section className="bg-white py-14 lg:py-16">
                <div className="site-container grid items-center gap-10 lg:grid-cols-[0.95fr_1.15fr] lg:gap-12">
                    <div>
                        <SparkLabel>Catering for Any Occasion</SparkLabel>
                        <h2 className="font-display text-[2rem] leading-tight font-semibold text-[#161616] sm:text-[2.35rem]">
                            Perfect Food for Every Celebration
                        </h2>
                        <p className="mt-4 text-[14px] leading-6 text-[#6b655c]">
                            Whether you’re planning a wedding, corporate lunch, or a family
                            milestone, we craft menus and service that fit your moment perfectly.
                        </p>

                        <ul className="mt-6 space-y-3">
                            {occasionList.map((item) => (
                                <li
                                    key={item}
                                    className="flex items-center gap-2.5 text-[14px] text-[#333]"
                                >
                                    <span
                                        className="flex h-5 w-5 items-center justify-center rounded-full text-white"
                                        style={{ background: GOLD }}
                                    >
                                        <Check className="h-3 w-3" strokeWidth={3} />
                                    </span>
                                    {item}
                                </li>
                            ))}
                        </ul>

                        <a
                            href="tel:+15551234567"
                            className="mt-8 inline-flex items-center gap-2 rounded-[5px] bg-[#1a1410] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#2a2118]"
                        >
                            <Phone className="h-4 w-4" style={{ color: GOLD }} />
                            Talk to Our Catering Expert
                        </a>
                    </div>

                    <div className="grid grid-cols-2 gap-3 sm:gap-4">
                        {occasionCards.map((card) => {
                            const Icon = occasionIcons[card.key];
                            return (
                                <div
                                    key={card.title}
                                    className="group relative aspect-[4/3] overflow-hidden rounded-xl"
                                >
                                    <img
                                        src={card.image}
                                        alt={card.title}
                                        className="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                        loading="lazy"
                                    />
                                    <div className="absolute inset-0 bg-gradient-to-t from-black/80 via-black/35 to-black/15" />
                                    <div className="absolute inset-0 flex flex-col items-center justify-center gap-2.5 p-3 text-center">
                                        <span
                                            className="flex h-11 w-11 items-center justify-center rounded-full text-white shadow-lg"
                                            style={{ background: GOLD }}
                                        >
                                            <Icon className="h-5 w-5" strokeWidth={1.75} />
                                        </span>
                                        <p className="font-display text-[1.05rem] font-semibold text-white sm:text-lg">
                                            {card.title}
                                        </p>
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                </div>
            </section>

            {/* Packages */}
            <section id="packages" className="bg-[#faf7f2] py-14 lg:py-16">
                <div className="site-container">
                    <div className="mb-9 text-center">
                        <div className="flex justify-center">
                            <SparkLabel>Our Catering Packages</SparkLabel>
                        </div>
                        <h2 className="font-display text-[2rem] font-semibold text-[#161616] sm:text-[2.35rem]">
                            Catering Packages{' '}
                            <span style={{ color: GOLD }}>→</span>
                        </h2>
                    </div>

                    <div className="grid gap-5 lg:grid-cols-[1fr_1fr_1fr_0.92fr] lg:gap-4 xl:gap-5">
                        {packages.map((pkg) => (
                            <div
                                key={pkg.id}
                                className={`relative flex flex-col overflow-hidden rounded-2xl border bg-white shadow-[0_10px_30px_rgba(13,13,13,0.05)] ${
                                    pkg.popular
                                        ? 'border-[#e8c48a] ring-1 ring-[#e8c48a]/60'
                                        : 'border-[#ebe4d8]'
                                }`}
                            >
                                {pkg.popular ? (
                                    <div
                                        className="absolute top-3 right-3 z-10 rounded-full px-3 py-1 text-[11px] font-semibold tracking-wide text-white uppercase"
                                        style={{ background: GOLD }}
                                    >
                                        Most Popular
                                    </div>
                                ) : null}

                                <img
                                    src={pkg.image}
                                    alt={pkg.name}
                                    className="h-36 w-full object-cover"
                                    loading="lazy"
                                />

                                <div className="flex flex-1 flex-col p-5">
                                    <h3 className="font-display text-[1.45rem] font-semibold text-[#161616]">
                                        {pkg.name}
                                    </h3>
                                    <p className="mt-1 text-[12px] text-[#6b655c]">{pkg.subtitle}</p>

                                    <ul className="mt-4 space-y-2.5">
                                        {pkg.features.map((f) => (
                                            <li
                                                key={f}
                                                className="flex items-start gap-2 text-[13px] text-[#3a342c]"
                                            >
                                                <Check
                                                    className="mt-0.5 h-4 w-4 shrink-0"
                                                    style={{ color: GOLD }}
                                                    strokeWidth={2.5}
                                                />
                                                {f}
                                            </li>
                                        ))}
                                    </ul>

                                    <div className="mt-auto pt-5">
                                        <p className="text-[12px] text-[#6b655c]">Starting from</p>
                                        <p className="font-display text-[1.65rem] font-semibold text-[#161616]">
                                            ${pkg.price}{' '}
                                            <span className="text-[13px] font-sans font-medium text-[#6b655c]">
                                                / Per Person
                                            </span>
                                        </p>
                                        <button
                                            type="button"
                                            onClick={scrollToQuote}
                                            className={`mt-4 inline-flex w-full items-center justify-center gap-2 rounded-[5px] px-4 py-2.5 text-sm font-semibold transition ${
                                                pkg.popular
                                                    ? 'text-white hover:brightness-110'
                                                    : 'border-2 bg-white hover:bg-[#c47a25]/08'
                                            }`}
                                            style={
                                                pkg.popular
                                                    ? { background: GOLD }
                                                    : { borderColor: GOLD, color: GOLD }
                                            }
                                        >
                                            Request Quote
                                        </button>
                                    </div>
                                </div>
                            </div>
                        ))}

                        {/* Custom quote card */}
                        <div className="flex flex-col gap-4">
                            <div className="flex flex-1 flex-col rounded-2xl border border-[#ebe4d8] bg-[#f3ebe0] p-6">
                                <h3 className="font-display text-[1.45rem] font-semibold text-[#161616]">
                                    Need Something Custom?
                                </h3>
                                <p className="mt-3 text-[13px] leading-6 text-[#5a544c]">
                                    We’ll create a personalized menu, staffing plan, and setup that
                                    matches your vision — big or small.
                                </p>
                                <button
                                    type="button"
                                    onClick={scrollToQuote}
                                    className="mt-6 inline-flex items-center justify-center gap-2 rounded-[5px] bg-[#1a1410] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#2a2118]"
                                >
                                    Get Custom Quote
                                    <ArrowRight className="h-4 w-4" />
                                </button>
                            </div>

                            <div
                                className="rounded-2xl px-5 py-4 text-white"
                                style={{ background: GOLD }}
                            >
                                <div className="flex items-center gap-3">
                                    <span className="flex h-10 w-10 items-center justify-center rounded-full bg-white/20">
                                        <Phone className="h-5 w-5" />
                                    </span>
                                    <div>
                                        <p className="text-[12px] text-white/85">Call us directly</p>
                                        <a
                                            href="tel:+15551234567"
                                            className="text-[15px] font-semibold hover:underline"
                                        >
                                            +1 (555) 123-4567
                                        </a>
                                        <p className="mt-0.5 text-[11px] text-white/80">
                                            Mon – Sun · 10:00 AM – 11:00 PM
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* Why choose */}
            <section className="bg-[#111111] py-14 text-white lg:py-16">
                <div className="site-container">
                    <div className="mb-10 flex justify-center text-center">
                        <SparkLabel light>
                            <span style={{ color: GOLD }}>Why Choose Bynnas Restora?</span>
                        </SparkLabel>
                    </div>

                    <div className="grid gap-8 sm:grid-cols-2 lg:grid-cols-5 lg:gap-5">
                        {whyChoose.map((item) => {
                            const Icon = whyIcons[item.key];
                            return (
                                <div key={item.title} className="text-center">
                                    <span
                                        className="mx-auto flex h-14 w-14 items-center justify-center rounded-full border"
                                        style={{ borderColor: `${GOLD}55`, color: GOLD }}
                                    >
                                        <Icon className="h-6 w-6" strokeWidth={1.5} />
                                    </span>
                                    <h3
                                        className="font-display mt-4 text-[1.15rem] font-semibold"
                                        style={{ color: GOLD }}
                                    >
                                        {item.title}
                                    </h3>
                                    <p className="mt-1.5 text-[12px] leading-5 text-white/60">
                                        {item.text}
                                    </p>
                                </div>
                            );
                        })}
                    </div>
                </div>
            </section>

            {/* Gallery */}
            <section className="bg-white py-14 lg:py-16">
                <div className="site-container">
                    <div className="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <SparkLabel>A Glimpse of Our Catering</SparkLabel>
                            <h2 className="font-display text-[2rem] font-semibold text-[#161616] sm:text-[2.2rem]">
                                A Glimpse of Our Catering
                            </h2>
                        </div>
                        <div className="flex gap-2">
                            <button
                                type="button"
                                aria-label="Previous photos"
                                disabled={!canPrev}
                                onClick={() => scrollGallery(-1)}
                                className="flex h-11 w-11 items-center justify-center rounded-full border border-[#e5ddd0] bg-white text-[#161616] transition hover:border-[#c47a25] hover:text-[#c47a25] disabled:cursor-not-allowed disabled:opacity-35"
                            >
                                <ArrowLeft className="h-4 w-4" />
                            </button>
                            <button
                                type="button"
                                aria-label="Next photos"
                                disabled={!canNext}
                                onClick={() => scrollGallery(1)}
                                className="flex h-11 w-11 items-center justify-center rounded-full border border-[#e5ddd0] bg-white text-[#161616] transition hover:border-[#c47a25] hover:text-[#c47a25] disabled:cursor-not-allowed disabled:opacity-35"
                            >
                                <ArrowRight className="h-4 w-4" />
                            </button>
                        </div>
                    </div>

                    <div
                        ref={galleryRef}
                        className="flex gap-4 overflow-x-auto scroll-smooth pb-2 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
                    >
                        {galleryImages.map((src) => (
                            <img
                                key={src}
                                src={src}
                                alt="Catering presentation"
                                className="h-48 w-[280px] shrink-0 rounded-xl object-cover sm:h-56 sm:w-[320px]"
                                loading="lazy"
                            />
                        ))}
                    </div>
                </div>
            </section>

            {/* Final CTA */}
            <section className="bg-[#faf7f2] pb-14 lg:pb-16">
                <div className="site-container">
                    <div className="relative overflow-hidden rounded-2xl bg-[#1a1410] px-6 py-9 text-white sm:px-10 sm:py-10">
                        <img
                            src={ctaDishImage}
                            alt=""
                            aria-hidden="true"
                            className="pointer-events-none absolute top-1/2 right-6 hidden h-40 w-40 -translate-y-1/2 rounded-full object-cover ring-4 ring-white/10 lg:block xl:right-10 xl:h-44 xl:w-44"
                        />

                        <div className="relative z-10 flex flex-col items-start justify-between gap-6 lg:flex-row lg:items-center lg:pr-52">
                            <div className="max-w-md">
                                <h2 className="font-display text-[1.85rem] leading-tight font-semibold sm:text-[2.1rem]">
                                    Let Us Make Your Event Truly Special!
                                </h2>
                                <p className="mt-2 text-[14px] text-white/65">
                                    Great food, great service, great memories.
                                </p>
                            </div>

                            <div className="text-left lg:text-center">
                                <button
                                    type="button"
                                    onClick={scrollToQuote}
                                    className="inline-flex items-center gap-2 rounded-[5px] px-6 py-3.5 text-sm font-semibold text-white transition hover:brightness-110"
                                    style={{ background: GOLD }}
                                >
                                    Request a Quote Today
                                    <ArrowRight className="h-4 w-4" />
                                </button>
                                <p className="mt-2 text-[12px] text-white/55">
                                    We’ll get back to you within 24 hours.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <Footer />
        </div>
    );
}
