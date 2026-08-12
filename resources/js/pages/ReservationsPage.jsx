import { useState } from 'react';
import { Link } from 'react-router-dom';
import {
    ArrowRight,
    CalendarCheck2,
    CalendarDays,
    Check,
    CheckCircle2,
    Clock3,
    Gift,
    Heart,
    Info,
    Leaf,
    Mail,
    MapPin,
    Pencil,
    Phone,
    RotateCcw,
    ShieldCheck,
    Star,
    Users,
} from 'lucide-react';
import Navbar from '../components/home/Navbar';
import Footer from '../components/home/Footer';
import {
    ORANGE,
    ambienceImages,
    heroPerks,
    occasions,
    promoImage,
    promoThumb,
    reservationsHeroImage,
    whyReserve,
} from '../data/reservationsStatic';

const perkIcons = {
    calendar: CalendarDays,
    check: CheckCircle2,
    star: Star,
};

const whyIcons = {
    seat: CalendarCheck2,
    clock: Clock3,
    gift: Gift,
    heart: Heart,
    shield: ShieldCheck,
};

const emptyForm = {
    date: '',
    time: '7:00 PM',
    guests: '2 Guests',
    name: '',
    phone: '',
    email: '',
    request: '',
};

function FieldLabel({ icon: Icon, children, required }) {
    return (
        <span className="mb-1.5 flex items-center gap-1.5 text-[12px] font-medium text-[#3a342c]">
            <Icon className="h-3.5 w-3.5" style={{ color: ORANGE }} />
            {children}
            {required ? <span className="text-red-500">*</span> : null}
        </span>
    );
}

export default function ReservationsPage() {
    const [form, setForm] = useState(emptyForm);
    const [submitted, setSubmitted] = useState(false);

    const update = (key, value) => setForm((prev) => ({ ...prev, [key]: value }));

    const onSubmit = (e) => {
        e.preventDefault();
        setSubmitted(true);
    };

    const onReset = () => {
        setForm(emptyForm);
        setSubmitted(false);
    };

    return (
        <div className="min-h-screen bg-[#faf7f2]">
            <Navbar />

            {/* Hero */}
            <section className="relative overflow-hidden bg-[#0d0d0d] text-white">
                <div className="absolute inset-0">
                    <img
                        src={reservationsHeroImage}
                        alt="Candlelit restaurant table"
                        className="h-full w-full scale-105 object-cover object-center blur-[1px]"
                    />
                    <div className="absolute inset-0 bg-[#0d0d0d]/70" />
                    <div className="absolute inset-0 bg-gradient-to-r from-black/55 via-black/35 to-black/20" />
                    <div className="absolute inset-x-0 bottom-0 h-28 bg-gradient-to-t from-[#faf7f2] to-transparent" />
                </div>

                <div className="relative z-10 site-container pt-28 pb-20 lg:pt-32 lg:pb-24">
                    <nav className="mb-6 flex items-center gap-2 text-[12px] text-white/70">
                        <Star className="h-3 w-3" style={{ color: ORANGE }} fill={ORANGE} />
                        <Link to="/" className="hover:text-white">
                            Home
                        </Link>
                        <span className="text-white/40">›</span>
                        <span style={{ color: ORANGE }}>Reservations</span>
                    </nav>

                    <h1 className="font-display max-w-3xl text-[2.6rem] leading-[1.08] font-semibold sm:text-5xl lg:text-[3.55rem]">
                        Reserve Your Table
                        <br />
                        <span style={{ color: ORANGE }}>For A Great Experience</span>
                    </h1>

                    <p className="mt-5 max-w-lg text-[14px] leading-6 text-white/78 sm:text-[15px]">
                        Good food is better when shared with the right people. Book your table and
                        let us take care of the rest.
                    </p>

                    <div className="mt-12 grid max-w-3xl gap-6 sm:grid-cols-3 sm:gap-8">
                        {heroPerks.map((perk) => {
                            const Icon = perkIcons[perk.key];
                            return (
                                <div key={perk.title} className="flex items-start gap-3">
                                    <span
                                        className="mt-0.5 flex h-11 w-11 shrink-0 items-center justify-center rounded-full border"
                                        style={{
                                            borderColor: `${ORANGE}55`,
                                            background: `${ORANGE}18`,
                                            color: ORANGE,
                                        }}
                                    >
                                        <Icon className="h-5 w-5" strokeWidth={1.75} />
                                    </span>
                                    <div>
                                        <p className="text-[14px] font-semibold text-white">
                                            {perk.title}
                                        </p>
                                        <p className="mt-0.5 text-[12px] leading-5 text-white/62">
                                            {perk.text}
                                        </p>
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                </div>
            </section>

            {/* Form + sidebars */}
            <section className="relative z-10 site-container -mt-6 pb-12 lg:pb-14">
                <div className="grid items-start gap-6 lg:grid-cols-[1.45fr_0.9fr] lg:gap-7">
                    {/* Form card */}
                    <div className="rounded-2xl border border-[#ebe4d8] bg-white p-5 shadow-[0_14px_42px_rgba(13,13,13,0.07)] sm:p-8">
                        <div className="mb-7 flex items-start gap-3">
                            <span
                                className="flex h-11 w-11 items-center justify-center rounded-full"
                                style={{ background: `${ORANGE}18`, color: ORANGE }}
                            >
                                <CalendarDays className="h-5 w-5" />
                            </span>
                            <div>
                                <h2 className="font-display text-[1.7rem] leading-none font-semibold text-[#111] sm:text-[1.85rem]">
                                    Book a Table
                                </h2>
                                <p className="mt-2 text-[13px] text-[#6f695f]">
                                    Fill out the form below and we’ll confirm your reservation.
                                </p>
                            </div>
                        </div>

                        {submitted ? (
                            <div className="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-6 text-center">
                                <CheckCircle2 className="mx-auto h-10 w-10 text-emerald-600" />
                                <p className="font-display mt-3 text-2xl font-semibold text-[#111]">
                                    Request Received
                                </p>
                                <p className="mt-2 text-sm text-[#5a544c]">
                                    Thanks{form.name ? `, ${form.name}` : ''}. We’ll confirm your
                                    table shortly via phone or email.
                                </p>
                                <button
                                    type="button"
                                    onClick={onReset}
                                    className="mt-5 inline-flex items-center gap-2 rounded-md border px-4 py-2.5 text-sm font-semibold"
                                    style={{ borderColor: ORANGE, color: ORANGE }}
                                >
                                    <RotateCcw className="h-4 w-4" />
                                    Book Another Table
                                </button>
                            </div>
                        ) : (
                            <form onSubmit={onSubmit} className="space-y-4">
                                <div className="grid gap-4 sm:grid-cols-3">
                                    <label className="block">
                                        <FieldLabel icon={CalendarDays} required>
                                            Select Date
                                        </FieldLabel>
                                        <input
                                            type="date"
                                            required
                                            value={form.date}
                                            onChange={(e) => update('date', e.target.value)}
                                            className="field"
                                        />
                                    </label>
                                    <label className="block">
                                        <FieldLabel icon={Clock3} required>
                                            Select Time
                                        </FieldLabel>
                                        <select
                                            className="field"
                                            value={form.time}
                                            onChange={(e) => update('time', e.target.value)}
                                        >
                                            {[
                                                '6:00 PM',
                                                '6:30 PM',
                                                '7:00 PM',
                                                '7:30 PM',
                                                '8:00 PM',
                                                '8:30 PM',
                                                '9:00 PM',
                                            ].map((t) => (
                                                <option key={t}>{t}</option>
                                            ))}
                                        </select>
                                    </label>
                                    <label className="block">
                                        <FieldLabel icon={Users} required>
                                            No. of Guests
                                        </FieldLabel>
                                        <select
                                            className="field"
                                            value={form.guests}
                                            onChange={(e) => update('guests', e.target.value)}
                                        >
                                            {[
                                                '1 Guest',
                                                '2 Guests',
                                                '3 Guests',
                                                '4 Guests',
                                                '5 Guests',
                                                '6+ Guests',
                                            ].map((g) => (
                                                <option key={g}>{g}</option>
                                            ))}
                                        </select>
                                    </label>
                                </div>

                                <div className="grid gap-4 sm:grid-cols-2">
                                    <label className="block">
                                        <FieldLabel icon={Users} required>
                                            Your Name
                                        </FieldLabel>
                                        <input
                                            type="text"
                                            required
                                            placeholder="Full name"
                                            value={form.name}
                                            onChange={(e) => update('name', e.target.value)}
                                            className="field"
                                        />
                                    </label>
                                    <label className="block">
                                        <FieldLabel icon={Phone} required>
                                            Phone Number
                                        </FieldLabel>
                                        <input
                                            type="tel"
                                            required
                                            placeholder="+1 (555) 000-0000"
                                            value={form.phone}
                                            onChange={(e) => update('phone', e.target.value)}
                                            className="field"
                                        />
                                    </label>
                                </div>

                                <div className="grid gap-4 sm:grid-cols-2">
                                    <label className="block">
                                        <FieldLabel icon={Mail} required>
                                            Email Address
                                        </FieldLabel>
                                        <input
                                            type="email"
                                            required
                                            placeholder="you@email.com"
                                            value={form.email}
                                            onChange={(e) => update('email', e.target.value)}
                                            className="field"
                                        />
                                    </label>
                                    <label className="block">
                                        <FieldLabel icon={Pencil}>
                                            Special Request (Optional)
                                        </FieldLabel>
                                        <input
                                            type="text"
                                            placeholder="Allergies, celebrations…"
                                            value={form.request}
                                            onChange={(e) => update('request', e.target.value)}
                                            className="field"
                                        />
                                    </label>
                                </div>

                                <div className="flex gap-3 rounded-xl border border-[#e8d7b8] bg-[#f9f3eb] px-4 py-3.5 text-[13px] leading-5 text-[#6a5a40]">
                                    <Info
                                        className="mt-0.5 h-4 w-4 shrink-0"
                                        style={{ color: '#3b82f6' }}
                                    />
                                    <p>
                                        Reservations will be held for 15 minutes past the reservation
                                        time. Please let us know if you’re running late.
                                    </p>
                                </div>

                                <div className="flex flex-col gap-3 pt-1 sm:flex-row">
                                    <button
                                        type="submit"
                                        className="btn-primary flex-[1.35] !py-3.5"
                                        style={{ background: ORANGE }}
                                    >
                                        Book My Table
                                        <ArrowRight className="h-4 w-4" />
                                    </button>
                                    <button
                                        type="button"
                                        onClick={onReset}
                                        className="inline-flex flex-1 items-center justify-center gap-2 rounded-md border-2 bg-white px-4 py-3.5 text-sm font-semibold transition hover:bg-[#c47a25]/08"
                                        style={{ borderColor: ORANGE, color: ORANGE }}
                                    >
                                        <RotateCcw className="h-4 w-4" />
                                        Reset Form
                                    </button>
                                </div>
                            </form>
                        )}
                    </div>

                    {/* Sidebars */}
                    <aside className="space-y-5">
                        <div className="rounded-2xl bg-[#141414] p-5 text-white shadow-lg sm:p-6">
                            <h3
                                className="font-display text-[1.35rem] font-semibold"
                                style={{ color: ORANGE }}
                            >
                                Restaurant Information
                            </h3>
                            <ul className="mt-5 space-y-4 text-[13px] leading-5 text-white/75">
                                <li className="flex items-start gap-3">
                                    <MapPin
                                        className="mt-0.5 h-4 w-4 shrink-0"
                                        style={{ color: ORANGE }}
                                    />
                                    123 Food Street, Culinary District, City 1207
                                </li>
                                <li className="flex items-start gap-3">
                                    <Clock3
                                        className="mt-0.5 h-4 w-4 shrink-0"
                                        style={{ color: ORANGE }}
                                    />
                                    Mon – Sun: 10:00 AM – 11:00 PM
                                </li>
                                <li className="flex items-center gap-3">
                                    <Phone className="h-4 w-4 shrink-0" style={{ color: ORANGE }} />
                                    <a href="tel:+15551234567" className="hover:text-white">
                                        +1 (555) 123-4567
                                    </a>
                                </li>
                                <li className="flex items-center gap-3">
                                    <Mail className="h-4 w-4 shrink-0" style={{ color: ORANGE }} />
                                    <a
                                        href="mailto:hello@bynnasrestora.com"
                                        className="hover:text-white"
                                    >
                                        hello@bynnasrestora.com
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div className="rounded-2xl border border-[#ebe4d8] bg-white p-5 shadow-sm sm:p-6">
                            <h3 className="font-display text-[1.35rem] font-semibold text-[#111]">
                                Restaurant Ambience
                            </h3>
                            <div className="mt-4 grid grid-cols-2 gap-2.5">
                                {ambienceImages.map((src) => (
                                    <img
                                        key={src}
                                        src={src}
                                        alt="Restaurant ambience"
                                        className="aspect-square rounded-lg object-cover"
                                        loading="lazy"
                                    />
                                ))}
                            </div>
                        </div>

                        <div className="rounded-2xl border border-[#ebe4d8] bg-white p-5 shadow-sm sm:p-6">
                            <h3 className="font-display text-[1.35rem] font-semibold text-[#111]">
                                Occasions We Host
                            </h3>
                            <ul className="mt-4 space-y-3">
                                {occasions.map((item) => (
                                    <li
                                        key={item}
                                        className="flex items-center gap-2.5 text-[13px] text-[#444]"
                                    >
                                        <span
                                            className="flex h-5 w-5 items-center justify-center rounded-full text-white"
                                            style={{ background: ORANGE }}
                                        >
                                            <Check className="h-3 w-3" strokeWidth={3} />
                                        </span>
                                        {item}
                                    </li>
                                ))}
                            </ul>
                        </div>
                    </aside>
                </div>
            </section>

            {/* Help banner */}
            <section className="bg-[#f0e4d4]">
                <div className="site-container flex flex-col items-start justify-between gap-4 py-7 sm:flex-row sm:items-center">
                    <div className="flex items-start gap-3.5">
                        <span
                            className="flex h-12 w-12 shrink-0 items-center justify-center rounded-full text-white"
                            style={{ background: ORANGE }}
                        >
                            <Phone className="h-5 w-5" />
                        </span>
                        <div>
                            <p className="font-display text-[1.35rem] font-semibold text-[#111]">
                                Need Help with Your Reservation?
                            </p>
                            <p className="mt-1 text-[13px] text-[#5a544c]">
                                Call us directly and our team will be happy to assist you.
                            </p>
                        </div>
                    </div>
                    <a
                        href="tel:+15551234567"
                        className="inline-flex items-center gap-2 rounded-full border bg-white px-5 py-2.5 text-sm font-semibold shadow-sm"
                        style={{ borderColor: `${ORANGE}55`, color: ORANGE }}
                    >
                        <Phone className="h-4 w-4" />
                        +1 (555) 123-4567
                    </a>
                </div>
            </section>

            {/* Why reserve */}
            <section className="bg-white py-14 lg:py-16">
                <div className="site-container">
                    <div className="mb-10 flex items-center justify-center gap-3 text-center">
                        <Leaf className="h-4 w-4" style={{ color: ORANGE }} />
                        <h2 className="font-display text-[2rem] font-semibold text-[#111] sm:text-[2.15rem]">
                            Why Reserve With Us?
                        </h2>
                        <Leaf className="h-4 w-4 scale-x-[-1]" style={{ color: ORANGE }} />
                    </div>

                    <div className="grid gap-8 sm:grid-cols-2 lg:grid-cols-5 lg:gap-5">
                        {whyReserve.map((item) => {
                            const Icon = whyIcons[item.key];
                            return (
                                <div key={item.title} className="text-center">
                                    <span
                                        className="mx-auto flex h-16 w-16 items-center justify-center rounded-full"
                                        style={{ background: '#f3ebe0', color: ORANGE }}
                                    >
                                        <Icon className="h-7 w-7" strokeWidth={1.5} />
                                    </span>
                                    <h3 className="font-display mt-4 text-[1.15rem] font-semibold text-[#111]">
                                        {item.title}
                                    </h3>
                                    <p className="mt-1.5 text-[12px] leading-5 text-[#6f695f]">
                                        {item.text}
                                    </p>
                                </div>
                            );
                        })}
                    </div>
                </div>
            </section>

            {/* Promo banner */}
            <section className="bg-[#faf7f2] pb-12 lg:pb-14">
                <div className="site-container">
                    <div className="relative overflow-hidden rounded-2xl bg-[#111] px-6 py-8 text-white sm:px-10 sm:py-9">
                        <img
                            src={promoThumb}
                            alt=""
                            aria-hidden="true"
                            className="pointer-events-none absolute top-1/2 left-0 hidden h-24 w-24 -translate-y-1/2 -translate-x-3 rounded-full object-cover opacity-50 ring-4 ring-white/10 lg:block"
                        />
                        <img
                            src={promoImage}
                            alt=""
                            aria-hidden="true"
                            className="pointer-events-none absolute top-1/2 right-0 hidden h-36 w-48 -translate-y-1/2 translate-x-4 rounded-l-[2rem] object-cover opacity-55 lg:block"
                        />

                        <div className="relative z-10 flex flex-col items-start justify-between gap-5 lg:flex-row lg:items-center lg:pl-16 lg:pr-36">
                            <div className="max-w-xl">
                                <p
                                    className="text-[12px] font-semibold tracking-[0.14em] uppercase"
                                    style={{ color: ORANGE }}
                                >
                                    Limited Time Offer
                                </p>
                                <p className="font-display mt-1 text-[1.85rem] leading-tight font-semibold sm:text-[2.05rem]">
                                    Book Now & Get 10% Off
                                </p>
                                <p className="mt-2 text-[13px] text-white/65">
                                    Use code{' '}
                                    <span className="font-semibold" style={{ color: ORANGE }}>
                                        BYNNAS10
                                    </span>{' '}
                                    for a special discount on your next reservation.
                                </p>
                            </div>
                            <button
                                type="button"
                                className="btn-primary shrink-0"
                                style={{ background: ORANGE }}
                                onClick={() =>
                                    window.scrollTo({ top: 360, behavior: 'smooth' })
                                }
                            >
                                Book Now & Save
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <Footer />
        </div>
    );
}
