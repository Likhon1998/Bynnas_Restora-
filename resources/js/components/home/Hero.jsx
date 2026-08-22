import { useEffect, useRef, useState } from 'react';
import { Link } from 'react-router-dom';
import {
    Award,
    ChefHat,
    Clock3,
    Leaf,
    Play,
    ShieldCheck,
    Star,
    UtensilsCrossed,
} from 'lucide-react';
import { getSiteSettings } from '../../data/siteSettings';

const featureIcons = {
    leaf: Leaf,
    chef: ChefHat,
    award: Award,
    shield: ShieldCheck,
};

export default function Hero() {
    const settings = getSiteSettings();
    const features = settings.hero_features?.length ? settings.hero_features : [];
    const stageRef = useRef(null);
    const [tilt, setTilt] = useState({ x: 0, y: 0 });
    const [reduceMotion, setReduceMotion] = useState(false);

    useEffect(() => {
        const mq = window.matchMedia('(prefers-reduced-motion: reduce)');
        setReduceMotion(mq.matches);
        const onChange = () => setReduceMotion(mq.matches);
        mq.addEventListener('change', onChange);
        return () => mq.removeEventListener('change', onChange);
    }, []);

    useEffect(() => {
        if (reduceMotion) return undefined;
        const node = stageRef.current;
        if (!node) return undefined;

        const onMove = (e) => {
            const rect = node.getBoundingClientRect();
            const px = (e.clientX - rect.left) / rect.width - 0.5;
            const py = (e.clientY - rect.top) / rect.height - 0.5;
            setTilt({ x: py * -6, y: px * 8 });
        };
        const onLeave = () => setTilt({ x: 0, y: 0 });

        node.addEventListener('pointermove', onMove);
        node.addEventListener('pointerleave', onLeave);
        return () => {
            node.removeEventListener('pointermove', onMove);
            node.removeEventListener('pointerleave', onLeave);
        };
    }, [reduceMotion]);

    return (
        <section id="home" className="hero-bg relative overflow-hidden text-white">
            <div className="absolute inset-0">
                <img
                    src={settings.hero_bg_url}
                    alt=""
                    aria-hidden="true"
                    className="hero-kenburns h-full w-full object-cover"
                />
                <div className="absolute inset-0 bg-[#070707]/55" />
                <div className="absolute inset-0 bg-gradient-to-r from-[#070707] via-[#070707]/88 to-[#070707]/35" />
                <div className="absolute inset-0 bg-gradient-to-t from-[#070707] via-transparent to-black/40" />
                <div className="hero-glow pointer-events-none absolute -top-24 right-[8%] h-[420px] w-[420px] rounded-full" />
                <div className="hero-glow-soft pointer-events-none absolute bottom-10 left-[18%] h-56 w-56 rounded-full" />
            </div>

            <div className="relative site-container pt-[5.75rem] pb-28 sm:pt-28 sm:pb-32 lg:pt-[6.5rem] lg:pb-36">
                <div className="grid items-center gap-10 lg:grid-cols-[1.05fr_0.95fr] lg:gap-8 xl:gap-12">
                    <div className="max-w-[580px]">
                        <div className="anim-up mb-4 flex items-center gap-2.5">
                            <span className="h-px w-7 bg-gold/70" />
                            <p className="text-[11px] font-semibold tracking-[0.2em] text-gold uppercase sm:text-[12px]">
                                {settings.hero_eyebrow}
                            </p>
                            <span className="h-px w-7 bg-gold/70" />
                        </div>

                        <h1 className="anim-up-1 font-display text-[2.7rem] leading-[1.02] font-semibold sm:text-[3.4rem] lg:text-[3.85rem]">
                            {settings.hero_headline}{' '}
                            <span className="relative inline-block text-gold">
                                {settings.hero_headline_accent}
                                <span className="hero-title-underline" aria-hidden="true" />
                            </span>
                        </h1>

                        <p className="anim-up-2 mt-5 max-w-md text-[14px] leading-7 text-white/74 sm:text-[15.5px]">
                            {settings.hero_description}
                        </p>

                        <div className="anim-up-3 mt-7 flex flex-wrap items-center gap-3.5">
                            <Link to="/menu" className="btn-primary hero-cta-primary">
                                <UtensilsCrossed className="h-4 w-4" />
                                Explore Our Menu
                            </Link>
                            <Link to="/about" className="btn-ghost hero-cta-ghost">
                                <span className="relative flex h-8 w-8 items-center justify-center rounded-full border border-gold/55 text-gold">
                                    <span className="hero-play-pulse absolute inset-0 rounded-full" />
                                    <Play className="relative h-3 w-3 fill-current" />
                                </span>
                                Watch Our Story
                            </Link>
                        </div>

                        <div className="anim-up-3 mt-9 grid grid-cols-2 gap-3.5 sm:grid-cols-4">
                            {features.map((item, index) => {
                                const Icon = featureIcons[item.key] || Award;
                                return (
                                    <div
                                        key={item.title}
                                        className="hero-feature group"
                                        style={{ animationDelay: `${0.28 + index * 0.06}s` }}
                                    >
                                        <span className="hero-feature-icon">
                                            <Icon className="h-4 w-4" strokeWidth={1.75} />
                                        </span>
                                        <div className="min-w-0">
                                            <p className="text-[12.5px] leading-tight font-semibold text-white">
                                                {item.title}
                                            </p>
                                            <p className="mt-1 text-[10.5px] text-white/48">
                                                {item.subtitle}
                                            </p>
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    </div>

                    <div
                        ref={stageRef}
                        className="relative mx-auto w-full max-w-[520px] perspective-[1200px]"
                    >
                        <div
                            className="hero-stage relative mx-auto w-full max-w-[460px] transition-transform duration-300 ease-out"
                            style={{
                                transform: reduceMotion
                                    ? undefined
                                    : `rotateX(${tilt.x}deg) rotateY(${tilt.y}deg)`,
                            }}
                        >
                            <div className="hero-ring hero-ring-outer" aria-hidden="true" />
                            <div className="hero-ring hero-ring-inner" aria-hidden="true" />

                            <div className="hero-plate">
                                <img
                                    src={settings.hero_image_url}
                                    alt={`${settings.restaurant_name} signature plate`}
                                    className="hero-plate-img"
                                />
                                <div className="hero-plate-shine" aria-hidden="true" />
                            </div>

                            <div className="hero-float-rating absolute top-[18%] -left-1 z-20 sm:-left-4 lg:-left-6">
                                <div className="flex items-center gap-2 rounded-full border border-white/10 bg-[#0d0d0d]/88 px-3 py-2 backdrop-blur-md">
                                    <span className="flex h-8 w-8 items-center justify-center rounded-full bg-ember text-white">
                                        <Star className="h-3.5 w-3.5 fill-current" />
                                    </span>
                                    <div>
                                        <p className="text-[13px] leading-none font-semibold text-white">
                                            4.9 / 5
                                        </p>
                                        <p className="mt-1 text-[10px] tracking-wide text-white/55 uppercase">
                                            Guest favorite
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div className="satisfaction-seal absolute -top-1 -right-1 z-20 grid place-content-center p-3 text-center sm:top-1 sm:right-0">
                                <div className="mb-1 flex justify-center gap-0.5">
                                    {Array.from({ length: 5 }).map((_, i) => (
                                        <Star
                                            key={i}
                                            className="h-2.5 w-2.5 fill-gold text-gold"
                                        />
                                    ))}
                                </div>
                                <p className="font-display text-[10px] leading-tight font-semibold tracking-[0.08em] text-gold uppercase">
                                    100%
                                    <br />
                                    Satisfaction
                                    <br />
                                    Guaranteed
                                </p>
                            </div>

                            <div className="hours-card absolute -bottom-1 left-1/2 z-20 flex w-[min(100%,300px)] -translate-x-1/2 items-center gap-3 px-3.5 py-2.5 sm:bottom-2 sm:left-auto sm:right-0 sm:w-auto sm:translate-x-0">
                                <span className="relative flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-ember/20 text-gold">
                                    <span className="hero-open-dot absolute top-1 right-1 h-2 w-2 rounded-full bg-emerald-400" />
                                    <Clock3 className="h-4 w-4" />
                                </span>
                                <div>
                                    <p className="text-[11px] font-semibold tracking-[0.12em] text-gold uppercase">
                                        We Are Open
                                    </p>
                                    <p className="text-[13px] font-medium text-white">
                                        {settings.opening_hours}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}
