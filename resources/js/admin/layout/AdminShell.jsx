import { useMemo, useState } from 'react';
import {
    AlertTriangle,
    BadgeCheck,
    Bell,
    BookOpen,
    Boxes,
    CalendarDays,
    ChevronDown,
    FileText,
    Grid2x2,
    Heart,
    LayoutDashboard,
    LogOut,
    Menu,
    MessageSquare,
    MonitorSmartphone,
    Percent,
    QrCode,
    Receipt,
    Search,
    Settings,
    Shield,
    ShoppingBag,
    Shuffle,
    Truck,
    UserRound,
    Users,
    Wallet,
    BarChart3,
    ChefHat,
    Plus,
    X,
} from 'lucide-react';
import { GOLD, navSections } from '../data/dashboardStatic';

const icons = {
    layout: LayoutDashboard,
    monitor: MonitorSmartphone,
    bag: ShoppingBag,
    calendar: CalendarDays,
    grid: Grid2x2,
    users: Users,
    heart: Heart,
    box: Boxes,
    book: BookOpen,
    truck: Truck,
    file: FileText,
    shuffle: Shuffle,
    alert: AlertTriangle,
    wallet: Wallet,
    chart: BarChart3,
    receipt: Receipt,
    percent: Percent,
    badge: BadgeCheck,
    shield: Shield,
    settings: Settings,
};

export default function AdminShell({ userName, userEmail, children }) {
    const [open, setOpen] = useState(false);
    const [active, setActive] = useState('dashboard');
    const firstName = useMemo(() => userName?.split(' ')[0] || 'Admin', [userName]);

    const NavBody = (
        <>
            <div className="flex items-center gap-2.5 px-4 py-5">
                <span
                    className="flex h-10 w-10 items-center justify-center rounded-xl"
                    style={{ background: `${GOLD}22`, color: GOLD }}
                >
                    <ChefHat className="h-5 w-5" />
                </span>
                <div>
                    <p className="font-display text-[1.15rem] leading-none font-semibold text-white">
                        Bynnas <span style={{ color: GOLD }}>Restora</span>
                    </p>
                    <p className="mt-1 text-[10px] tracking-[0.14em] text-white/40 uppercase">
                        Admin Panel
                    </p>
                </div>
            </div>

            <nav className="flex-1 space-y-4 overflow-y-auto px-3 pb-4">
                {navSections.map((section) => (
                    <div key={section.title || 'main'}>
                        {section.title ? (
                            <p className="mb-2 px-2 text-[10px] font-semibold tracking-[0.16em] text-white/35 uppercase">
                                {section.title}
                            </p>
                        ) : null}
                        <ul className="space-y-1">
                            {section.items.map((item) => {
                                const Icon = icons[item.icon] || LayoutDashboard;
                                const isActive = active === item.key;
                                const className = `flex w-full items-center gap-2.5 rounded-lg px-2.5 py-2 text-left text-[13px] transition ${
                                    isActive
                                        ? 'bg-white/5 text-[#e0a84a]'
                                        : 'text-white/65 hover:bg-white/4 hover:text-white'
                                }`;
                                const style = isActive
                                    ? { boxShadow: `inset 3px 0 0 ${GOLD}` }
                                    : undefined;
                                const content = (
                                    <>
                                        <Icon className="h-4 w-4 shrink-0 opacity-90" />
                                        <span className="flex-1 truncate">{item.label}</span>
                                        {item.badge ? (
                                            <span
                                                className="rounded-full px-1.5 py-0.5 text-[10px] font-semibold text-white"
                                                style={{ background: GOLD }}
                                            >
                                                {item.badge}
                                            </span>
                                        ) : null}
                                    </>
                                );

                                return (
                                    <li key={item.key}>
                                        {item.href ? (
                                            <a href={item.href} className={className} style={style}>
                                                {content}
                                            </a>
                                        ) : (
                                            <button
                                                type="button"
                                                onClick={() => {
                                                    setActive(item.key);
                                                    setOpen(false);
                                                }}
                                                className={className}
                                                style={style}
                                            >
                                                {content}
                                            </button>
                                        )}
                                    </li>
                                );
                            })}
                        </ul>
                    </div>
                ))}
            </nav>

            <div className="m-3 rounded-xl border border-white/10 bg-gradient-to-br from-[#c47a25]/20 to-transparent p-3">
                <p className="text-[11px] font-semibold tracking-wide text-[#e0a84a] uppercase">
                    Your Plan
                </p>
                <p className="mt-1 text-sm font-semibold text-white">Premium</p>
                <p className="mt-0.5 text-[11px] text-white/45">Expires Dec 31, 2026</p>
                <button
                    type="button"
                    className="mt-3 w-full rounded-lg py-2 text-xs font-semibold text-white"
                    style={{ background: GOLD }}
                >
                    View Plan
                </button>
            </div>
        </>
    );

    return (
        <div className="admin-shell min-h-screen bg-[#0b0e14] text-white">
            {/* Mobile drawer */}
            {open ? (
                <div className="fixed inset-0 z-40 lg:hidden">
                    <button
                        type="button"
                        className="absolute inset-0 bg-black/60"
                        aria-label="Close menu"
                        onClick={() => setOpen(false)}
                    />
                    <aside className="absolute inset-y-0 left-0 flex w-[280px] flex-col border-r border-white/8 bg-[#0f131b]">
                        <button
                            type="button"
                            className="absolute top-3 right-3 rounded-lg p-1.5 text-white/60 hover:bg-white/5"
                            onClick={() => setOpen(false)}
                        >
                            <X className="h-4 w-4" />
                        </button>
                        {NavBody}
                    </aside>
                </div>
            ) : null}

            <div className="lg:grid lg:grid-cols-[260px_1fr]">
                <aside className="sticky top-0 hidden h-screen flex-col border-r border-white/8 bg-[#0f131b] lg:flex">
                    {NavBody}
                </aside>

                <div className="min-w-0">
                    <header className="sticky top-0 z-30 border-b border-white/8 bg-[#0b0e14]/90 backdrop-blur-md">
                        <div className="flex flex-wrap items-center gap-3 px-4 py-3 lg:px-6">
                            <button
                                type="button"
                                className="rounded-lg border border-white/10 p-2 text-white/70 lg:hidden"
                                onClick={() => setOpen(true)}
                            >
                                <Menu className="h-4 w-4" />
                            </button>

                            <label className="relative min-w-0 flex-1 lg:max-w-md">
                                <Search className="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-white/35" />
                                <input
                                    type="search"
                                    placeholder="Search orders, menu items, customers..."
                                    className="w-full rounded-xl border border-white/10 bg-white/5 py-2.5 pr-16 pl-10 text-sm text-white outline-none placeholder:text-white/35 focus:border-[#c47a25]/50"
                                />
                                <span className="absolute top-1/2 right-3 hidden -translate-y-1/2 rounded border border-white/10 px-1.5 py-0.5 text-[10px] text-white/35 sm:inline">
                                    Ctrl K
                                </span>
                            </label>

                            <div className="ml-auto flex flex-wrap items-center gap-2">
                                {[
                                    { label: 'New Order', icon: Plus },
                                    { label: 'Reservation', icon: CalendarDays },
                                    { label: 'Walk-in', icon: UserRound },
                                    { label: 'QR Order', icon: QrCode },
                                ].map(({ label, icon: Icon }) => (
                                    <button
                                        key={label}
                                        type="button"
                                        className="hidden items-center gap-1.5 rounded-lg border border-white/10 bg-white/5 px-2.5 py-2 text-[12px] font-medium text-white/80 transition hover:border-[#c47a25]/40 hover:text-white xl:inline-flex"
                                    >
                                        <Icon className="h-3.5 w-3.5" style={{ color: GOLD }} />
                                        {label}
                                    </button>
                                ))}

                                <button
                                    type="button"
                                    className="relative rounded-lg border border-white/10 p-2 text-white/70"
                                >
                                    <Bell className="h-4 w-4" />
                                    <span className="absolute -top-1 -right-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[9px] font-bold">
                                        5
                                    </span>
                                </button>
                                <button
                                    type="button"
                                    className="relative rounded-lg border border-white/10 p-2 text-white/70"
                                >
                                    <MessageSquare className="h-4 w-4" />
                                    <span className="absolute -top-1 -right-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[9px] font-bold">
                                        2
                                    </span>
                                </button>

                                <div className="flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 py-1.5 pr-2 pl-1.5">
                                    <span className="flex h-8 w-8 items-center justify-center rounded-full bg-[#c47a25]/25 text-sm font-semibold text-[#e0a84a]">
                                        {(userName || 'A').charAt(0)}
                                    </span>
                                    <div className="hidden sm:block">
                                        <p className="text-[11px] text-white/45">Welcome back,</p>
                                        <p className="text-[12px] leading-tight font-semibold">
                                            {userName}
                                        </p>
                                    </div>
                                    <ChevronDown className="hidden h-3.5 w-3.5 text-white/40 sm:block" />
                                </div>

                                <form method="POST" action="/logout">
                                    <input
                                        type="hidden"
                                        name="_token"
                                        value={
                                            document
                                                .querySelector('meta[name="csrf-token"]')
                                                ?.getAttribute('content') || ''
                                        }
                                    />
                                    <button
                                        type="submit"
                                        className="rounded-lg border border-white/10 p-2 text-white/60 hover:text-white"
                                        title="Sign out"
                                    >
                                        <LogOut className="h-4 w-4" />
                                    </button>
                                </form>
                            </div>
                        </div>
                    </header>

                    <main className="px-4 py-5 lg:px-6 lg:py-6">
                        {typeof children === 'function'
                            ? children({ firstName, userEmail, active })
                            : children}
                    </main>

                    <footer className="flex flex-wrap items-center justify-between gap-2 border-t border-white/8 px-4 py-4 text-[11px] text-white/35 lg:px-6">
                        <p>© {new Date().getFullYear()} Bynnas Restora</p>
                        <div className="flex gap-3">
                            <span>Privacy Policy</span>
                            <span>Terms & Conditions</span>
                            <span>Support</span>
                        </div>
                    </footer>
                </div>
            </div>
        </div>
    );
}
