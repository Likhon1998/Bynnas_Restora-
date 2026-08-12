import { Link } from 'react-router-dom';
import {
    CalendarDays,
    ChefHat,
    Clock3,
    Heart,
    Leaf,
    MapPin,
    Medal,
    Play,
    ShieldCheck,
    Smile,
    Sparkle,
    Star,
    Users,
    UtensilsCrossed,
} from 'lucide-react';
import Navbar from '../components/home/Navbar';
import Newsletter from '../components/home/Newsletter';
import Footer from '../components/home/Footer';
import {
    FacebookIcon,
    InstagramIcon,
    TwitterIcon,
} from '../components/icons/BrandIcons';
import {
    GOLD,
    aboutHeroImage,
    aboutStats,
    aboutStoryImages,
    aboutTeam,
    aboutValues,
    guestCtaImage,
} from '../data/aboutStatic';

const valueIcons = { leaf: Leaf, heart: Heart, users: Users, star: Star };
const statIcons = { smile: Smile, dish: UtensilsCrossed, award: Medal, pin: MapPin };

export default function AboutPage() {
    return (
        <div className="min-h-screen bg-[#fcfbf7] text-ink">
            <Navbar />

            {/* HERO */}
            <section className="relative overflow-hidden bg-[#0a0a0a] text-white">
                <div className="absolute inset-0">
                    <img
                        src={aboutHeroImage}
                        alt="Restaurant interior"
                        className="h-full w-full scale-105 object-cover object-center blur-[1px]"
                    />
                    <div className="absolute inset-0 bg-[#0a0a0a]/70" />
                    <div className="absolute inset-0 bg-gradient-to-r from-[#0a0a0a]/95 via-[#0a0a0a]/75 to-[#0a0a0a]/35" />
                </div>

                {/* Large watermark logo */}
                <div
                    className="pointer-events-none absolute top-[48%] right-[5%] z-[1] hidden -translate-y-1/2 select-none flex-col items-center lg:flex xl:right-[8%]"
                    style={{ opacity: 0.22 }}
                >
                    <div
                        className="flex h-40 w-40 items-center justify-center rounded-full border-[3px]"
                        style={{ borderColor: GOLD }}
                    >
                        <ChefHat className="h-20 w-20" style={{ color: GOLD }} strokeWidth={1.1} />
                    </div>
                    <p
                        className="font-display mt-5 text-center text-[1.75rem] tracking-[0.04em]"
                        style={{ color: GOLD }}
                    >
                        Bynnas Restora
                    </p>
                </div>

                <div className="relative z-10 site-container py-28 lg:py-36">
                    <div className="max-w-[540px]">
                        <div className="mb-3 flex items-center gap-2">
                            <Sparkle className="h-3.5 w-3.5 fill-current" style={{ color: GOLD }} />
                            <span
                                className="text-[12px] font-semibold tracking-[0.18em] uppercase"
                                style={{ color: GOLD }}
                            >
                                Get to Know Us
                            </span>
                            <Sparkle className="h-3.5 w-3.5 fill-current" style={{ color: GOLD }} />
                        </div>

                        <h1 className="font-display text-[2.6rem] leading-[1.08] font-semibold sm:text-[3.4rem] lg:text-[3.85rem]">
                            Our Passion,
                            <br />
                            <span style={{ color: GOLD }}>Your Experience</span>
                        </h1>

                        <p className="mt-4 max-w-[430px] text-[14px] leading-6 text-white/75 sm:text-[15px] sm:leading-7">
                            We believe great food creates lasting memories. From our kitchen to your
                            table, every detail is crafted with warmth, flavor, and care.
                        </p>

                        <a
                            href="#story"
                            className="mt-7 inline-flex items-center gap-3 rounded-[4px] border-2 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-white/5"
                            style={{ borderColor: GOLD }}
                        >
                            <span
                                className="flex h-8 w-8 items-center justify-center rounded-full border"
                                style={{ borderColor: GOLD, color: GOLD }}
                            >
                                <Play className="h-3.5 w-3.5 fill-current" />
                            </span>
                            Watch Our Story
                        </a>
                    </div>
                </div>
            </section>

            {/* OUR STORY */}
            <section id="story" className="bg-[#fcfbf7] py-12 lg:py-14">
                <div className="site-container grid items-center gap-10 lg:grid-cols-2 lg:gap-12">
                    <div>
                        <p
                            className="inline-flex items-center gap-2 text-[11px] font-bold tracking-[0.2em] uppercase"
                            style={{ color: GOLD }}
                        >
                            <UtensilsCrossed className="h-3.5 w-3.5" />
                            Our Story
                        </p>

                        <h2 className="font-display mt-2.5 text-[1.9rem] leading-[1.15] font-semibold text-[#111] sm:text-[2.25rem]">
                            A Journey That Started
                            <br />
                            with a <span style={{ color: GOLD }}>Simple Dream</span>
                        </h2>

                        <div
                            className="mt-4 h-[2px] w-14 rounded-full"
                            style={{ background: GOLD }}
                        />

                        <p className="mt-5 text-[14px] leading-7 text-[#5a544c]">
                            Bynnas Restora began as a small kitchen with a big heart — a place where
                            family recipes, seasonal ingredients, and genuine hospitality could come
                            together on every plate.
                        </p>
                        <p className="mt-3 text-[14px] leading-7 text-[#5a544c]">
                            Over the years we’ve grown, but our promise hasn’t changed: cook with
                            passion, serve with warmth, and make every guest feel at home.
                        </p>

                        <div className="mt-7">
                            <p
                                className="font-script text-[2.6rem] leading-none"
                                style={{ color: GOLD }}
                            >
                                Chef Bynnas
                            </p>
                            <p className="mt-1 text-[13px] text-[#7a7368]">Founder & Head Chef</p>
                        </div>
                    </div>

                    {/* Image collage */}
                    <div className="grid grid-cols-[1.2fr_0.8fr] gap-3">
                        <div className="relative overflow-hidden rounded-[18px]">
                            <img
                                src={aboutStoryImages.chef}
                                alt="Chef plating food"
                                className="h-[340px] w-full object-cover sm:h-[460px]"
                                loading="lazy"
                            />
                            <div
                                className="absolute bottom-4 left-4 flex h-[112px] w-[112px] flex-col items-center justify-center rounded-full border-[3px] bg-[#111]/95 text-center shadow-xl"
                                style={{ borderColor: GOLD }}
                            >
                                <ChefHat className="mb-1 h-4 w-4" style={{ color: GOLD }} />
                                <p
                                    className="font-display text-[1.25rem] leading-none font-semibold"
                                    style={{ color: GOLD }}
                                >
                                    15+
                                </p>
                                <p className="mt-1 px-2 text-[8px] leading-tight tracking-[0.06em] text-white/80 uppercase">
                                    Years of Experience
                                </p>
                            </div>
                        </div>
                        <div className="flex flex-col gap-3">
                            <div className="flex-1 overflow-hidden rounded-[18px]">
                                <img
                                    src={aboutStoryImages.interior}
                                    alt="Dining room"
                                    className="h-[164px] w-full object-cover sm:h-[224px]"
                                    loading="lazy"
                                />
                            </div>
                            <div className="flex-1 overflow-hidden rounded-[18px]">
                                <img
                                    src={aboutStoryImages.prep}
                                    alt="Preparing dough"
                                    className="h-[164px] w-full object-cover sm:h-[224px]"
                                    loading="lazy"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* VALUES */}
            <section className="bg-[#f3ebe0] py-10 lg:py-12">
                <div className="site-container grid gap-8 lg:grid-cols-[230px_1fr] lg:items-start lg:gap-10">
                    <div>
                        <p
                            className="text-[11px] font-bold tracking-[0.2em] uppercase"
                            style={{ color: GOLD }}
                        >
                            Our Values
                        </p>
                        <h2 className="font-display mt-2 text-[1.75rem] leading-tight font-semibold text-[#111] lg:text-[2rem]">
                            The Principles That
                            <br />
                            Drive Us
                        </h2>
                        <div
                            className="mt-3 h-[2px] w-12 rounded-full"
                            style={{ background: GOLD }}
                        />
                    </div>

                    <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-4 lg:gap-0">
                        {aboutValues.map((item, i) => {
                            const Icon = valueIcons[item.key];
                            return (
                                <div
                                    key={item.title}
                                    className={`lg:px-5 ${i > 0 ? 'lg:border-l lg:border-[#d8cfbf]' : ''}`}
                                >
                                    <Icon
                                        className="h-[22px] w-[22px]"
                                        style={{ color: GOLD }}
                                        strokeWidth={1.5}
                                    />
                                    <h3 className="font-display mt-3 text-[1.15rem] font-semibold text-[#111]">
                                        {item.title}
                                    </h3>
                                    <p className="mt-1.5 text-[13px] leading-5 text-[#6f695f]">
                                        {item.text}
                                    </p>
                                </div>
                            );
                        })}
                    </div>
                </div>
            </section>

            {/* STATS */}
            <section className="bg-[#111]">
                <div className="site-container grid sm:grid-cols-2 lg:grid-cols-4">
                    {aboutStats.map((stat, i) => {
                        const Icon = statIcons[stat.key];
                        return (
                            <div
                                key={stat.label}
                                className={`flex flex-col items-center px-3 py-8 text-center ${
                                    i > 0 ? 'border-white/10 sm:border-l' : ''
                                }`}
                            >
                                <Icon
                                    className="h-6 w-6"
                                    style={{ color: GOLD }}
                                    strokeWidth={1.4}
                                />
                                <p className="font-display mt-2.5 text-[2rem] leading-none font-semibold text-white lg:text-[2.35rem]">
                                    {stat.value}
                                </p>
                                <p className="mt-2 text-[13px] font-medium text-white/90">
                                    {stat.label}
                                </p>
                                <p className="mt-0.5 text-[12px]" style={{ color: GOLD }}>
                                    {stat.sub}
                                </p>
                            </div>
                        );
                    })}
                </div>
            </section>

            {/* TEAM */}
            <section className="bg-[#fcfbf7] py-12 lg:py-14">
                <div className="site-container grid gap-8 lg:grid-cols-[240px_1fr] lg:gap-10">
                    <div>
                        <p
                            className="text-[11px] font-bold tracking-[0.2em] uppercase"
                            style={{ color: GOLD }}
                        >
                            Our Team
                        </p>
                        <h2 className="font-display mt-2 text-[1.8rem] leading-tight font-semibold text-[#111] lg:text-[2.15rem]">
                            The People Behind
                            <br />
                            Bynnas Restora
                        </h2>
                        <p className="mt-3 text-[13px] leading-6 text-[#6f695f]">
                            A passionate crew of chefs, hosts, and hospitality pros dedicated to
                            making every visit unforgettable.
                        </p>
                        <button
                            type="button"
                            className="mt-5 inline-flex items-center gap-2 rounded-[4px] border-2 px-4 py-2.5 text-sm font-semibold transition hover:text-white"
                            style={{ borderColor: GOLD, color: GOLD }}
                            onMouseEnter={(e) => {
                                e.currentTarget.style.background = GOLD;
                                e.currentTarget.style.color = '#111';
                            }}
                            onMouseLeave={(e) => {
                                e.currentTarget.style.background = 'transparent';
                                e.currentTarget.style.color = GOLD;
                            }}
                        >
                            <Users className="h-4 w-4" />
                            Join Our Team
                        </button>
                    </div>

                    <div className="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                        {aboutTeam.map((m) => (
                            <article key={m.name} className="text-center">
                                <div className="overflow-hidden rounded-[16px]">
                                    <img
                                        src={m.image}
                                        alt={m.name}
                                        className="aspect-[3/4] w-full object-cover"
                                        loading="lazy"
                                    />
                                </div>
                                <h3 className="font-display mt-3 text-[1.15rem] font-semibold text-[#111]">
                                    {m.name}
                                </h3>
                                <p className="mt-0.5 text-[12px] font-medium" style={{ color: GOLD }}>
                                    {m.role}
                                </p>
                                <div className="mt-2.5 flex justify-center gap-2">
                                    {[FacebookIcon, InstagramIcon, TwitterIcon].map((Icon, idx) => (
                                        <a
                                            key={idx}
                                            href="#contact"
                                            className="flex h-7 w-7 items-center justify-center rounded-full border border-[#e5dccf] text-[#6f695f] transition hover:text-white"
                                            style={{ borderColor: '#e5dccf' }}
                                            onMouseEnter={(e) => {
                                                e.currentTarget.style.background = GOLD;
                                                e.currentTarget.style.borderColor = GOLD;
                                                e.currentTarget.style.color = '#fff';
                                            }}
                                            onMouseLeave={(e) => {
                                                e.currentTarget.style.background = 'transparent';
                                                e.currentTarget.style.borderColor = '#e5dccf';
                                                e.currentTarget.style.color = '#6f695f';
                                            }}
                                            aria-label="Social"
                                        >
                                            <Icon className="h-3 w-3" />
                                        </a>
                                    ))}
                                </div>
                            </article>
                        ))}
                    </div>
                </div>
            </section>

            {/* CTA BAR */}
            <section className="bg-[#161616]">
                <div className="site-container flex flex-col items-center gap-6 py-8 lg:flex-row lg:items-center lg:justify-between lg:gap-6 lg:py-9">
                    <div className="flex items-center gap-4">
                        <img
                            src={guestCtaImage}
                            alt="Fresh tomatoes and herbs"
                            className="hidden h-[72px] w-[72px] rounded-xl object-cover sm:block"
                        />
                        <div className="text-center sm:text-left">
                            <p className="font-display text-[1.45rem] leading-tight font-semibold text-white sm:text-[1.6rem]">
                                Be Our Guest
                            </p>
                            <p
                                className="font-display text-[1.45rem] leading-tight font-semibold sm:text-[1.6rem]"
                                style={{ color: GOLD }}
                            >
                                We Can’t Wait to Serve You!
                            </p>
                        </div>
                    </div>

                    <Link
                        to="/reservations"
                        className="inline-flex items-center gap-2 rounded-[4px] border-2 px-5 py-2.5 text-sm font-semibold transition"
                        style={{ borderColor: GOLD, color: GOLD }}
                    >
                        <CalendarDays className="h-4 w-4" />
                        Book a Table Now
                    </Link>

                    <div className="flex flex-wrap justify-center gap-4 text-[11px] text-white/75 sm:gap-5">
                        <span className="inline-flex items-center gap-1.5">
                            <CalendarDays className="h-3.5 w-3.5" style={{ color: GOLD }} />
                            Easy Reservation
                        </span>
                        <span className="inline-flex items-center gap-1.5">
                            <ShieldCheck className="h-3.5 w-3.5" style={{ color: GOLD }} />
                            Best Price Guarantee
                        </span>
                        <span className="inline-flex items-center gap-1.5">
                            <Clock3 className="h-3.5 w-3.5" style={{ color: GOLD }} />
                            Open 7 Days
                        </span>
                    </div>
                </div>
            </section>

            <Newsletter />
            <Footer />
        </div>
    );
}
