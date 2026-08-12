export function InstagramIcon({ className = 'h-4 w-4' }) {
    return (
        <svg className={className} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <rect x="3" y="3" width="18" height="18" rx="5" stroke="currentColor" strokeWidth="1.8" />
            <circle cx="12" cy="12" r="4" stroke="currentColor" strokeWidth="1.8" />
            <circle cx="17.5" cy="6.5" r="1.2" fill="currentColor" />
        </svg>
    );
}

export function FacebookIcon({ className = 'h-4 w-4' }) {
    return (
        <svg className={className} viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M14 9h3V6h-3c-2.2 0-4 1.8-4 4v2H8v3h2v7h3v-7h2.6l.4-3H13v-2c0-.6.4-1 1-1z" />
        </svg>
    );
}

export function TwitterIcon({ className = 'h-4 w-4' }) {
    return (
        <svg className={className} viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M18.9 3H21l-6.5 7.4L22 21h-5.7l-4.5-5.9L6.6 21H4.5l7-8L2 3h5.8l4 5.3L18.9 3zm-1 16.2h1.6L7.2 4.7H5.5l12.4 14.5z" />
        </svg>
    );
}

export function YoutubeIcon({ className = 'h-4 w-4' }) {
    return (
        <svg className={className} viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M22 12.2c0-2.3-.3-3.9-.5-4.6-.3-.9-.9-1.5-1.8-1.8C18.2 5.4 12 5.4 12 5.4s-6.2 0-7.7.4c-.9.3-1.5.9-1.8 1.8-.3.8-.5 2.3-.5 4.6s.3 3.9.5 4.6c.3.9.9 1.5 1.8 1.8 1.5.4 7.7.4 7.7.4s6.2 0 7.7-.4c.9-.3 1.5-.9 1.8-1.8.3-.8.5-2.3.5-4.6zM10.3 15.1V9.3l5.1 2.9-5.1 2.9z" />
        </svg>
    );
}
