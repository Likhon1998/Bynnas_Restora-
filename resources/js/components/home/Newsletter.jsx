import { Mail } from 'lucide-react';
import Reveal from '../ui/Reveal';

export default function Newsletter() {
    return (
        <section className="newsletter-panel">
            <div className="site-container py-12 md:py-14 lg:py-16">
                <Reveal>
                    <div className="flex flex-col items-center justify-between gap-6 md:flex-row md:gap-8">
                        <div className="flex items-center gap-3.5 text-center md:text-left">
                            <span className="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-ember text-white shadow-lg shadow-ember/30">
                                <Mail className="h-6 w-6" />
                            </span>
                            <div>
                                <p className="text-xs font-semibold tracking-[0.18em] text-gold-soft uppercase">
                                    Stay Updated
                                </p>
                                <h2 className="font-display mt-0.5 text-2xl font-semibold text-white md:text-[1.75rem]">
                                    Subscribe to Our Newsletter
                                </h2>
                            </div>
                        </div>

                        <form className="w-full max-w-xl" onSubmit={(e) => e.preventDefault()}>
                            <div className="flex flex-col gap-3 sm:flex-row">
                                <input
                                    type="email"
                                    required
                                    placeholder="Enter your email address"
                                    className="min-w-0 flex-1 rounded-[4px] border border-white/15 bg-white/8 px-4 py-3.5 text-sm text-white outline-none placeholder:text-white/45 focus:border-gold"
                                />
                                <button type="submit" className="btn-primary whitespace-nowrap">
                                    Subscribe
                                </button>
                            </div>
                            <p className="mt-2 text-[11px] text-white/40">
                                By subscribing you agree to our Privacy Policy. No spam —
                                unsubscribe anytime.
                            </p>
                        </form>
                    </div>
                </Reveal>
            </div>
        </section>
    );
}
