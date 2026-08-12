import { useLocation } from 'react-router-dom';

/** Shared nav destinations across marketing pages. */
export const siteNavLinks = [
    { label: 'Home', to: '/' },
    { label: 'Menu', to: '/menu' },
    { label: 'About Us', to: '/about' },
    { label: 'Reservations', to: '/reservations' },
    { label: 'Catering', to: '/catering' },
    { label: 'Blog', to: '/blog' },
    { label: 'Contact', to: '/contact' },
];

export default function useActiveNav() {
    const { pathname } = useLocation();

    const isActive = (to) => {
        if (to === '/') return pathname === '/';
        if (to === '/menu') return pathname.startsWith('/menu');
        if (to === '/about') return pathname.startsWith('/about');
        if (to === '/reservations') return pathname.startsWith('/reservations');
        if (to === '/catering') return pathname.startsWith('/catering');
        if (to === '/blog') return pathname.startsWith('/blog');
        if (to === '/contact') return pathname.startsWith('/contact');
        return false;
    };

    return { pathname, isActive };
}
