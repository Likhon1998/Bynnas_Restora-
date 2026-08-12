import { useEffect, useRef, useState } from 'react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { InstagramIcon } from '../icons/BrandIcons';
import { getSiteSettings } from '../../data/siteSettings';
import Reveal from '../ui/Reveal';

export default function Instagram() {
    const settings = getSiteSettings();
    const photos = settings.gallery_images?.length ? settings.gallery_images : [];
    const scrollerRef = useRef(null);
    const [canPrev, setCanPrev] = useState(false);
    const [canNext, setCanNext] = useState(true);

    const sync = () => {
        const el = scrollerRef.current;
        if (!el) return;
        setCanPrev(el.scrollLeft > 8);
        setCanNext(el.scrollLeft + el.clientWidth < el.scrollWidth - 8);
    };

    useEffect(() => {
        const el = scrollerRef.current;
        if (!el) return undefined;
        sync();
        el.addEventListener('scroll', sync, { passive: true });
        window.addEventListener('resize', sync);
        return () => {
            el.removeEventListener('scroll', sync);
            window.removeEventListener('resize', sync);
        };
    }, [photos.length]);

    const scrollByDir = (dir) => {
        const el = scrollerRef.current;
        if (!el) return;
        const amount = Math.min(el.clientWidth * 0.75, 320);
        el.scrollBy({ left: dir * amount, behavior: 'smooth' });
    };

    if (photos.length === 0) return null;

    const igHref = settings.social_instagram || 'https://instagram.com';

    return (
        <section id="blog" className="section-pad bg-[#111111]">
            <div className="site-container">
                <Reveal>
                    <div className="mb-8 flex flex-col items-center text-center">
                        <span className="mb-3 flex h-11 w-11 items-center justify-center rounded-full border border-[#c47a25]/45 text-[#e0a84a]">
                            <InstagramIcon className="h-5 w-5" />
                        </span>
                        <h2 className="font-display text-3xl font-semibold text-white md:text-[2.35rem]">
                            Follow Us On Instagram
                        </h2>
                        <p className="mt-2 text-sm tracking-wide text-white/55">
                            {settings.instagram_handle}
                        </p>
                    </div>
                </Reveal>

                <Reveal delay={80}>
                    <div className="relative">
                        <button
                            type="button"
                            aria-label="Previous photos"
                            disabled={!canPrev}
                            onClick={() => scrollByDir(-1)}
                            className="absolute top-1/2 -left-1 z-10 hidden h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-[#c47a25] text-white shadow-lg transition hover:brightness-110 disabled:cursor-not-allowed disabled:opacity-35 md:flex lg:-left-3"
                        >
                            <ChevronLeft className="h-4 w-4" />
                        </button>
                        <button
                            type="button"
                            aria-label="Next photos"
                            disabled={!canNext}
                            onClick={() => scrollByDir(1)}
                            className="absolute top-1/2 -right-1 z-10 hidden h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-[#c47a25] text-white shadow-lg transition hover:brightness-110 disabled:cursor-not-allowed disabled:opacity-35 md:flex lg:-right-3"
                        >
                            <ChevronRight className="h-4 w-4" />
                        </button>

                        <div
                            ref={scrollerRef}
                            className="flex gap-3 overflow-x-auto scroll-smooth px-1 pb-1 [-ms-overflow-style:none] [scrollbar-width:none] sm:gap-3.5 [&::-webkit-scrollbar]:hidden"
                        >
                            {photos.map((src, i) => (
                                <a
                                    key={`${src}-${i}`}
                                    href={igHref}
                                    target="_blank"
                                    rel="noreferrer"
                                    className="group relative aspect-square w-[min(42vw,160px)] shrink-0 overflow-hidden rounded-xl sm:w-[170px] md:w-[180px] lg:w-[min(15.5vw,190px)]"
                                >
                                    <img
                                        src={src}
                                        alt={`Gallery highlight ${i + 1}`}
                                        loading="lazy"
                                        className="h-full w-full object-cover transition duration-500 group-hover:scale-110"
                                    />
                                    <span className="absolute inset-0 bg-gradient-to-t from-black/55 via-black/10 to-transparent opacity-70 transition group-hover:opacity-90" />
                                    <span className="absolute inset-0 flex items-center justify-center opacity-0 transition group-hover:opacity-100">
                                        <span className="flex h-11 w-11 items-center justify-center rounded-full bg-white/15 text-white backdrop-blur-sm ring-1 ring-white/35">
                                            <InstagramIcon className="h-5 w-5" />
                                        </span>
                                    </span>
                                </a>
                            ))}
                        </div>
                    </div>
                </Reveal>
            </div>
        </section>
    );
}
