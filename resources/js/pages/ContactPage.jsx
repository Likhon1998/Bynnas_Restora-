import { useState } from 'react';
import { Link } from 'react-router-dom';
import {
    ArrowRight,
    ChevronDown,
    Clock3,
    Info,
    Mail,
    MapPin,
    Navigation,
    Phone,
    Send,
    Sparkles,
    User,
} from 'lucide-react';
import Navbar from '../components/home/Navbar';
import Footer from '../components/home/Footer';
import {
    GOLD,
    contactFaqs,
    contactHeroImage,
    eventPromoImage,
    mapEmbedUrl,
    newsletterSpiceImage,
    subjectOptions,
} from '../data/contactStatic';
import { buildContactCards, getSiteSettings } from '../data/siteSettings';

const cardIcons = {
    location: MapPin,
    phone: Phone,
    email: Mail,
    hours: Clock3,
};

const emptyForm = {
    name: '',
    email: '',
    phone: '',
    subject: 'General Inquiry',
    message: '',
};

export default function ContactPage() {
    const [form, setForm] = useState(emptyForm);
    const [sent, setSent] = useState(false);
    const [openFaq, setOpenFaq] = useState(0);
    const [email, setEmail] = useState('');
    const [subscribed, setSubscribed] = useState(false);
    const contactCards = buildContactCards(getSiteSettings());

    const update = (key, value) => setForm((prev) => ({ ...prev, [key]: value }));

    return (
        <div className="min-h-screen bg-[#faf7f2]">
            <Navbar />

            {/* Hero */}
            <section className="relative overflow-hidden bg-[#0d0d0d] text-white">
                <div className="absolute inset-0">
                    <img
                        src={contactHeroImage}
                        alt="Restaurant dining room"
                        className="h-full w-full object-cover object-center"
                    />
                    <div className="absolute inset-0 bg-[#0d0d0d]/68" />
                    <div className="absolute inset-0 bg-gradient-to-r from-black/75 via-black/50 to-black/30" />
                </div>

                <div className="relative z-10 site-container flex items-end justify-between gap-8 pt-28 pb-20 lg:pt-32 lg:pb-24">
                    <div className="max-w-xl">
                        <div className="mb-3 flex items-center gap-2">
                            <Sparkles className="h-3.5 w-3.5" style={{ color: GOLD }} fill={GOLD} />
                            <span
                                className="text-[12px] font-semibold tracking-[0.16em] uppercase"
                                style={{ color: GOLD }}
                            >
                                Get In Touch
                            </span>
                            <Sparkles className="h-3.5 w-3.5" style={{ color: GOLD }} fill={GOLD} />
                        </div>

                        <h1 className="font-display text-[2.5rem] leading-[1.1] font-semibold sm:text-5xl lg:text-[3.4rem]">
                            We’d Love to{' '}
                            <span className="text-white">Hear</span>{' '}
                            <span style={{ color: GOLD }}>From You!</span>
                        </h1>

                        <p className="mt-4 max-w-md text-[14px] leading-6 text-white/75 sm:text-[15px]">
                            Questions, special requests, or just saying hello — we’re here and happy
                            to help make your next visit unforgettable.
                        </p>
                    </div>

                    <p
                        className="font-script hidden max-w-[200px] text-right text-3xl leading-tight opacity-80 lg:block xl:text-4xl"
                        style={{ color: GOLD }}
                    >
                        Good Food
                        <br />
                        Great Mood
                    </p>
                </div>
            </section>

            {/* Info cards */}
            <section className="relative z-10 site-container -mt-10 pb-4">
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    {contactCards.map((card) => {
                        const Icon = cardIcons[card.key];
                        return (
                            <div
                                key={card.key}
                                className="rounded-2xl border border-[#ebe4d8] bg-white p-5 shadow-[0_12px_32px_rgba(13,13,13,0.06)]"
                            >
                                <span
                                    className="flex h-11 w-11 items-center justify-center rounded-full text-white"
                                    style={{ background: GOLD }}
                                >
                                    <Icon className="h-5 w-5" strokeWidth={1.75} />
                                </span>
                                <h3 className="font-display mt-4 text-lg font-semibold text-[#161616]">
                                    {card.title}
                                </h3>
                                {card.lines.map((line) => (
                                    <p key={line} className="mt-1 text-[13px] leading-5 text-[#6b655c]">
                                        {line}
                                    </p>
                                ))}
                            </div>
                        );
                    })}
                </div>
            </section>

            {/* Form + map */}
            <section className="site-container py-10 lg:py-12">
                <div className="grid items-start gap-8 lg:grid-cols-[1.15fr_0.95fr] lg:gap-10">
                    {/* Form */}
                    <div>
                        <h2 className="font-display flex items-center gap-2 text-[1.85rem] font-semibold text-[#161616]">
                            Send Us a Message
                            <ArrowRight className="h-5 w-5" style={{ color: GOLD }} />
                        </h2>
                        <p className="mt-2 text-[14px] text-[#6b655c]">
                            Fill out the form below and our team will get back to you as soon as
                            possible.
                        </p>

                        {sent ? (
                            <div className="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-8 text-center">
                                <Send className="mx-auto h-9 w-9 text-emerald-600" />
                                <p className="font-display mt-3 text-2xl font-semibold text-[#161616]">
                                    Message Sent
                                </p>
                                <p className="mt-2 text-sm text-[#5a544c]">
                                    Thanks{form.name ? `, ${form.name}` : ''}. We’ll reply within 24
                                    hours.
                                </p>
                                <button
                                    type="button"
                                    onClick={() => {
                                        setForm(emptyForm);
                                        setSent(false);
                                    }}
                                    className="mt-5 inline-flex items-center gap-2 rounded-md border px-4 py-2.5 text-sm font-semibold"
                                    style={{ borderColor: GOLD, color: GOLD }}
                                >
                                    Send Another Message
                                </button>
                            </div>
                        ) : (
                            <form
                                className="mt-6 space-y-4"
                                onSubmit={(e) => {
                                    e.preventDefault();
                                    setSent(true);
                                }}
                            >
                                <div className="grid gap-4 sm:grid-cols-2">
                                    <label className="block">
                                        <span className="mb-1.5 flex items-center gap-1.5 text-[12px] font-medium text-[#3a342c]">
                                            <User className="h-3.5 w-3.5" style={{ color: GOLD }} />
                                            Your Name <span className="text-red-500">*</span>
                                        </span>
                                        <input
                                            required
                                            type="text"
                                            placeholder="Full name"
                                            value={form.name}
                                            onChange={(e) => update('name', e.target.value)}
                                            className="field"
                                        />
                                    </label>
                                    <label className="block">
                                        <span className="mb-1.5 flex items-center gap-1.5 text-[12px] font-medium text-[#3a342c]">
                                            <Mail className="h-3.5 w-3.5" style={{ color: GOLD }} />
                                            Email Address <span className="text-red-500">*</span>
                                        </span>
                                        <input
                                            required
                                            type="email"
                                            placeholder="you@email.com"
                                            value={form.email}
                                            onChange={(e) => update('email', e.target.value)}
                                            className="field"
                                        />
                                    </label>
                                </div>

                                <div className="grid gap-4 sm:grid-cols-2">
                                    <label className="block">
                                        <span className="mb-1.5 flex items-center gap-1.5 text-[12px] font-medium text-[#3a342c]">
                                            <Phone className="h-3.5 w-3.5" style={{ color: GOLD }} />
                                            Phone Number
                                        </span>
                                        <input
                                            type="tel"
                                            placeholder="+1 (555) 000-0000"
                                            value={form.phone}
                                            onChange={(e) => update('phone', e.target.value)}
                                            className="field"
                                        />
                                    </label>
                                    <label className="block">
                                        <span className="mb-1.5 flex items-center gap-1.5 text-[12px] font-medium text-[#3a342c]">
                                            <Info className="h-3.5 w-3.5" style={{ color: GOLD }} />
                                            Subject
                                        </span>
                                        <select
                                            className="field"
                                            value={form.subject}
                                            onChange={(e) => update('subject', e.target.value)}
                                        >
                                            {subjectOptions.map((s) => (
                                                <option key={s}>{s}</option>
                                            ))}
                                        </select>
                                    </label>
                                </div>

                                <label className="block">
                                    <span className="mb-1.5 block text-[12px] font-medium text-[#3a342c]">
                                        Message <span className="text-red-500">*</span>
                                    </span>
                                    <textarea
                                        required
                                        rows={5}
                                        placeholder="How can we help you?"
                                        value={form.message}
                                        onChange={(e) => update('message', e.target.value)}
                                        className="field resize-y"
                                    />
                                </label>

                                <button
                                    type="submit"
                                    className="inline-flex w-full items-center justify-center gap-2 rounded-[5px] py-3.5 text-sm font-semibold text-white transition hover:brightness-110"
                                    style={{ background: GOLD }}
                                >
                                    Send Message
                                    <ArrowRight className="h-4 w-4" />
                                </button>
                            </form>
                        )}
                    </div>

                    {/* Map */}
                    <div>
                        <div className="mb-3 h-0.5 w-10" style={{ background: GOLD }} />
                        <h2 className="font-display text-[1.85rem] font-semibold text-[#161616]">
                            Find Us Here
                        </h2>

                        <div className="mt-4 overflow-hidden rounded-2xl border border-[#ebe4d8] bg-white shadow-sm">
                            <iframe
                                title="Bynnas Restora location map"
                                src={mapEmbedUrl}
                                className="h-64 w-full border-0 sm:h-72"
                                loading="lazy"
                                referrerPolicy="no-referrer-when-downgrade"
                            />
                        </div>

                        <div className="mt-5 rounded-2xl border border-[#ebe4d8] bg-white p-5 shadow-sm">
                            <h3
                                className="font-display text-xl font-semibold"
                                style={{ color: GOLD }}
                            >
                                Visit Our Restaurant
                            </h3>
                            <p className="mt-2 text-[13px] leading-6 text-[#6b655c]">
                                We’re located in the heart of Flavor Town. Easy to find, impossible
                                to forget!
                            </p>
                            <a
                                href="https://www.openstreetmap.org/?mlat=40.709&mlon=-74.005#map=16/40.709/-74.005"
                                target="_blank"
                                rel="noreferrer"
                                className="mt-4 inline-flex items-center gap-2 rounded-[5px] border-2 bg-white px-4 py-2.5 text-sm font-semibold transition hover:bg-[#c47a25]/08"
                                style={{ borderColor: GOLD, color: GOLD }}
                            >
                                <Navigation className="h-4 w-4" />
                                Get Directions
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            {/* Event promo + FAQ */}
            <section className="site-container pb-10 lg:pb-12">
                <div className="grid items-stretch gap-6 lg:grid-cols-2">
                    <div className="overflow-hidden rounded-2xl border border-[#ebe4d8] bg-white shadow-sm sm:grid sm:grid-cols-[200px_1fr]">
                        <img
                            src={eventPromoImage}
                            alt="Event dining setup"
                            className="h-44 w-full object-cover sm:h-full"
                            loading="lazy"
                        />
                        <div className="flex flex-col justify-center p-5 sm:p-6">
                            <h3 className="font-display text-[1.35rem] font-semibold text-[#161616]">
                                Planning an Event or Catering?
                            </h3>
                            <p className="mt-2 text-[13px] leading-6 text-[#6b655c]">
                                From intimate dinners to large celebrations, our catering team will
                                craft the perfect menu and service for your occasion.
                            </p>
                            <Link
                                to="/catering"
                                className="mt-4 inline-flex items-center gap-2 text-sm font-semibold transition hover:gap-3"
                                style={{ color: GOLD }}
                            >
                                Contact Our Catering Team
                                <ArrowRight className="h-4 w-4" />
                            </Link>
                        </div>
                    </div>

                    <div className="rounded-2xl border border-[#ebe4d8] bg-white p-5 shadow-sm sm:p-6">
                        <h3 className="font-display text-[1.45rem] font-semibold text-[#161616]">
                            Frequently Asked Questions
                        </h3>
                        <div className="mt-4 space-y-2">
                            {contactFaqs.map((faq, i) => {
                                const open = openFaq === i;
                                return (
                                    <div
                                        key={faq.q}
                                        className="overflow-hidden rounded-xl border border-[#ebe4d8]"
                                    >
                                        <button
                                            type="button"
                                            onClick={() => setOpenFaq(open ? -1 : i)}
                                            className="flex w-full items-center justify-between gap-3 px-4 py-3.5 text-left text-[14px] font-semibold text-[#2b2620]"
                                        >
                                            {faq.q}
                                            <ChevronDown
                                                className={`h-4 w-4 shrink-0 transition ${
                                                    open ? 'rotate-180' : ''
                                                }`}
                                                style={{ color: GOLD }}
                                            />
                                        </button>
                                        {open ? (
                                            <p className="border-t border-[#ebe4d8] px-4 py-3 text-[13px] leading-6 text-[#6b655c]">
                                                {faq.a}
                                            </p>
                                        ) : null}
                                    </div>
                                );
                            })}
                        </div>
                        <button
                            type="button"
                            className="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-[5px] py-3 text-sm font-semibold text-white transition hover:brightness-110"
                            style={{ background: GOLD }}
                        >
                            <Info className="h-4 w-4" />
                            View More FAQs
                        </button>
                    </div>
                </div>
            </section>

            {/* Newsletter */}
            <section className="site-container pb-12 lg:pb-14">
                <div className="relative overflow-hidden rounded-2xl bg-[#161616] px-6 py-8 text-white sm:px-8 sm:py-9">
                    <img
                        src={newsletterSpiceImage}
                        alt=""
                        aria-hidden="true"
                        className="pointer-events-none absolute top-1/2 right-0 hidden h-36 w-40 -translate-y-1/2 rounded-l-2xl object-cover opacity-45 lg:block"
                    />

                    <div className="relative z-10 flex flex-col items-start justify-between gap-6 lg:flex-row lg:items-center lg:pr-44">
                        <div className="flex items-start gap-4">
                            <span
                                className="flex h-14 w-14 shrink-0 items-center justify-center rounded-full text-white"
                                style={{ background: GOLD }}
                            >
                                <Mail className="h-6 w-6" />
                            </span>
                            <div>
                                <p
                                    className="text-[12px] font-semibold tracking-[0.14em] uppercase"
                                    style={{ color: GOLD }}
                                >
                                    Stay Connected
                                </p>
                                <h2 className="font-display mt-0.5 text-[1.65rem] font-semibold sm:text-[1.85rem]">
                                    Subscribe to Our Newsletter
                                </h2>
                                <p className="mt-1 text-[13px] text-white/60">
                                    Recipes, events, and exclusive offers — straight to your inbox.
                                </p>
                            </div>
                        </div>

                        {subscribed ? (
                            <p className="rounded-lg bg-white/10 px-4 py-3 text-sm text-white/85">
                                You’re subscribed — thank you!
                            </p>
                        ) : (
                            <form
                                className="flex w-full max-w-md flex-col gap-3 sm:flex-row"
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
                                    placeholder="Enter your email address"
                                    className="min-w-0 flex-1 rounded-[5px] border border-white/15 bg-white/8 px-4 py-3 text-sm text-white outline-none placeholder:text-white/40 focus:border-[#c47a25]"
                                />
                                <button
                                    type="submit"
                                    className="shrink-0 rounded-[5px] px-5 py-3 text-sm font-semibold text-white transition hover:brightness-110"
                                    style={{ background: GOLD }}
                                >
                                    Subscribe
                                </button>
                            </form>
                        )}
                    </div>
                </div>
            </section>

            <Footer />
        </div>
    );
}
