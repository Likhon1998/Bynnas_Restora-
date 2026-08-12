import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { CalendarDays, ChefHat, Menu, Search, ShoppingCart, X } from 'lucide-react';
import { siteNavLinks } from '../../data/nav';
import useActiveNav from '../../data/nav';
import useScrollSpy from '../../hooks/useScrollSpy';

function Logo() {
    return (
        <Link to="/" className="logo-mark shrink-0">
            <span className="logo-top">
                <ChefHat className="h-4 w-4 text-white" strokeWidth={2} />
            </span>
            <span className="logo-row">
                <span className="brand-script">Bynnas</span>
                <span className="brand-serif">Restora</span>
            </span>
            <span className="brand-tag">Good Food, Great Mood</span>
        </Link>
    );
}

export { Logo };

const HOME_SECTION_IDS = ['home', 'menu', 'about', 'reservations', 'catering', 'blog', 'contact'];

export default function Navbar({ cartCount = 0 }) {
    const [scrolled, setScrolled] = useState(false);
    const [open, setOpen] = useState(false);
    const { pathname, isActive } = useActiveNav();
    const activeSection = useScrollSpy(HOME_SECTION_IDS, 120);
    const onHome = pathname === '/';

    useEffect(() => {
        const onScroll = () => setScrolled(window.scrollY > 10);
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
        return () => window.removeEventListener('scroll', onScroll);
    }, []);

    useEffect(() => {
        document.body.classList.toggle('menu-open', open);
        return () => document.body.classList.remove('menu-open');
    }, [open]);

    const linkActive = (link) => {
        if (link.to === '/menu') return isActive('/menu');
        if (link.to === '/about') return isActive('/about');
        if (link.to === '/reservations') return isActive('/reservations');
        if (link.to === '/catering') return isActive('/catering');
        if (link.to === '/blog') return isActive('/blog');
        if (link.to === '/contact') return isActive('/contact');
        if (link.to === '/') return onHome && (activeSection === 'home' || activeSection === '');
        if (onHome && link.to.includes('#')) {
            return activeSection === link.to.split('#')[1];
        }
        return false;
    };

    return (
        <header
            className={`fixed inset-x-0 top-0 z-50 transition-all duration-300 ${
                scrolled || open || !onHome
                    ? 'bg-ink/95 shadow-[0_8px_28px_rgba(0,0,0,0.3)] backdrop-blur-md'
                    : 'bg-transparent'
            }`}
        >
            <div className="site-container flex h-[70px] items-center justify-between gap-4 lg:h-[76px]">
                <Logo />

                <nav className="hidden items-center gap-6 xl:flex" aria-label="Primary">
                    {siteNavLinks.map((link) => {
                        const active = linkActive(link);
                        return (
                            <Link
                                key={link.label}
                                to={link.to}
                                className={`relative text-[14px] font-medium tracking-wide transition ${
                                    active ? 'text-gold' : 'text-white/88 hover:text-white'
                                }`}
                            >
                                {link.label}
                                <span
                                    className={`absolute -bottom-1 left-0 h-[2px] rounded-full bg-gold transition-all ${
                                        active ? 'w-full' : 'w-0'
                                    }`}
                                />
                            </Link>
                        );
                    })}
                </nav>

                <div className="flex items-center gap-1.5 sm:gap-2">
                    <button
                        type="button"
                        aria-label="Search"
                        className="rounded-full p-2.5 text-white/90 transition hover:bg-white/10"
                    >
                        <Search className="h-5 w-5" />
                    </button>
                    <Link
                        to="/menu"
                        aria-label="Cart"
                        className="relative rounded-full p-2.5 text-white/90 transition hover:bg-white/10"
                    >
                        <ShoppingCart className="h-5 w-5" />
                        <span className="absolute top-1 right-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-ember px-1 text-[10px] font-bold text-white">
                            {cartCount}
                        </span>
                    </Link>
                    <Link to="/reservations" className="btn-primary hidden sm:inline-flex !py-2.5">
                        <CalendarDays className="h-4 w-4" />
                        Book a Table
                    </Link>
                    <button
                        type="button"
                        className="rounded-md p-2 text-white xl:hidden"
                        aria-label={open ? 'Close menu' : 'Open menu'}
                        aria-expanded={open}
                        onClick={() => setOpen((v) => !v)}
                    >
                        {open ? <X className="h-6 w-6" /> : <Menu className="h-6 w-6" />}
                    </button>
                </div>
            </div>

            <div
                className={`overflow-hidden border-t border-white/10 bg-ink/98 transition-all duration-300 xl:hidden ${
                    open ? 'max-h-[420px] opacity-100' : 'max-h-0 opacity-0'
                }`}
            >
                <div className="site-container flex flex-col gap-1 py-4">
                    {siteNavLinks.map((link) => (
                        <Link
                            key={link.label}
                            to={link.to}
                            onClick={() => setOpen(false)}
                            className={`rounded-md px-2 py-2.5 text-base ${
                                linkActive(link) ? 'bg-white/5 text-gold' : 'text-white/90'
                            }`}
                        >
                            {link.label}
                        </Link>
                    ))}
                    <Link
                        to="/reservations"
                        onClick={() => setOpen(false)}
                        className="btn-primary mt-3"
                    >
                        <CalendarDays className="h-4 w-4" />
                        Book a Table
                    </Link>
                </div>
            </div>
        </header>
    );
}
