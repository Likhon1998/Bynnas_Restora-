import { Link, useLocation } from 'react-router-dom';
import { Clock3, Mail, MapPin, Phone } from 'lucide-react';
import {
    FacebookIcon,
    InstagramIcon,
    TwitterIcon,
    YoutubeIcon,
} from '../icons/BrandIcons';
import { Logo } from './Navbar';
import { getSiteSettings } from '../../data/siteSettings';

const quickLinks = [
    { label: 'Home', to: '/' },
    { label: 'Menu', to: '/menu' },
    { label: 'About Us', to: '/about' },
    { label: 'Reservations', to: '/reservations' },
    { label: 'Catering', to: '/catering' },
    { label: 'Blog', to: '/blog' },
    { label: 'Contact Us', to: '/contact' },
];

const services = [
    'Dine In',
    'Takeaway',
    'Catering',
    'Private Events',
    'Online Ordering',
    'Gift Cards',
];

export default function Footer() {
    const { pathname } = useLocation();
    const settings = getSiteSettings();
    const address = [settings.address_line1, settings.address_line2, settings.city]
        .filter(Boolean)
        .join(', ');

    return (
        <footer id="contact" className="bg-ink text-white">
            <div className="site-container grid gap-8 py-10 sm:grid-cols-2 lg:grid-cols-4 lg:gap-6 lg:py-12">
                <div className="sm:col-span-2 lg:col-span-1">
                    <Logo />
                    <p className="mt-5 max-w-sm text-sm leading-relaxed text-white/60">
                        {settings.footer_note ||
                            'A neighborhood destination for memorable dining — fresh ingredients, warm hospitality, and flavors crafted with care.'}
                    </p>
                    <div className="mt-5 flex gap-3">
                        {[
                            { Icon: FacebookIcon, label: 'Facebook', href: settings.social_facebook },
                            { Icon: InstagramIcon, label: 'Instagram', href: settings.social_instagram },
                            { Icon: TwitterIcon, label: 'Twitter', href: settings.social_twitter },
                            { Icon: YoutubeIcon, label: 'YouTube', href: settings.social_youtube },
                        ].map(({ Icon, label, href }) => (
                            <a
                                key={label}
                                href={href || '#contact'}
                                target={href ? '_blank' : undefined}
                                rel={href ? 'noreferrer' : undefined}
                                className="flex h-9 w-9 items-center justify-center rounded-full bg-white/5 text-white/80 ring-1 ring-white/10 transition hover:bg-ember hover:text-white"
                                aria-label={label}
                            >
                                <Icon className="h-4 w-4" />
                            </a>
                        ))}
                    </div>
                </div>

                <div>
                    <h3 className="font-display text-xl font-semibold text-gold-soft">Quick Links</h3>
                    <ul className="mt-4 space-y-2.5">
                        {quickLinks.map((item) => {
                            const active =
                                (item.to === '/' && pathname === '/') ||
                                (item.to !== '/' && pathname.startsWith(item.to));
                            return (
                                <li key={item.label}>
                                    <Link
                                        to={item.to}
                                        className={`text-sm transition ${
                                            active
                                                ? 'font-medium text-ember'
                                                : 'text-white/65 hover:text-white'
                                        }`}
                                    >
                                        {item.label}
                                    </Link>
                                </li>
                            );
                        })}
                    </ul>
                </div>

                <div id="catering">
                    <h3 className="font-display text-xl font-semibold text-gold-soft">
                        Our Services
                    </h3>
                    <ul className="mt-4 space-y-2.5">
                        {services.map((item) => (
                            <li key={item}>
                                <Link
                                    to={item === 'Catering' ? '/catering' : '/menu'}
                                    className="text-sm text-white/65 transition hover:text-white"
                                >
                                    {item}
                                </Link>
                            </li>
                        ))}
                    </ul>
                </div>

                <div>
                    <h3 className="font-display text-xl font-semibold text-gold-soft">Contact Us</h3>
                    <ul className="mt-4 space-y-3.5 text-sm text-white/65">
                        <li className="flex items-start gap-3">
                            <MapPin className="mt-0.5 h-4 w-4 shrink-0 text-ember" />
                            <span>{address || 'Address coming soon'}</span>
                        </li>
                        <li className="flex items-center gap-3">
                            <Phone className="h-4 w-4 shrink-0 text-ember" />
                            <a href={`tel:${settings.phone || ''}`} className="hover:text-white">
                                {settings.phone || '—'}
                            </a>
                        </li>
                        <li className="flex items-center gap-3">
                            <Mail className="h-4 w-4 shrink-0 text-ember" />
                            <a href={`mailto:${settings.email || ''}`} className="hover:text-white">
                                {settings.email || '—'}
                            </a>
                        </li>
                        <li className="flex items-start gap-3">
                            <Clock3 className="mt-0.5 h-4 w-4 shrink-0 text-ember" />
                            <span>{settings.opening_hours || '—'}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div className="border-t border-white/10">
                <div className="site-container flex flex-col items-center justify-between gap-3 py-5 text-xs text-white/50 sm:flex-row">
                    <p>
                        © {new Date().getFullYear()} {settings.restaurant_name || 'Bynnas Restora'}.
                        All Rights Reserved.
                    </p>
                    <div className="flex gap-4">
                        <a href="#contact" className="transition hover:text-white">
                            Privacy Policy
                        </a>
                        <a href="#contact" className="transition hover:text-white">
                            Terms & Conditions
                        </a>
                    </div>
                </div>
            </div>
        </footer>
    );
}
