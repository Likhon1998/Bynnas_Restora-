import { useEffect, useState } from 'react';

/**
 * Tracks which section id is in view for nav highlighting.
 */
export default function useScrollSpy(ids, offset = 120) {
    const [activeId, setActiveId] = useState(ids[0] ?? '');

    useEffect(() => {
        const elements = ids
            .map((id) => document.getElementById(id))
            .filter(Boolean);

        if (!elements.length) return undefined;

        const onScroll = () => {
            const y = window.scrollY + offset;
            let current = ids[0];

            for (const el of elements) {
                if (el.offsetTop <= y) {
                    current = el.id;
                }
            }

            setActiveId(current);
        };

        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
        window.addEventListener('resize', onScroll);
        return () => {
            window.removeEventListener('scroll', onScroll);
            window.removeEventListener('resize', onScroll);
        };
    }, [ids, offset]);

    return activeId;
}
