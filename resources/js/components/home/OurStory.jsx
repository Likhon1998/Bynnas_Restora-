import { Check, Trophy } from 'lucide-react';
import { images, storyPoints } from '../../data/homeStatic';
import Reveal from '../ui/Reveal';

export default function OurStory() {
    return (
        <section id="about" className="section-pad bg-cream">
            <div className="site-container grid items-center gap-8 lg:grid-cols-2 lg:gap-10">
                <Reveal>
                    <div className="relative grid grid-cols-2 gap-3">
                        <div className="relative col-span-2 overflow-hidden rounded-2xl sm:col-span-1 sm:row-span-2 sm:min-h-[400px]">
                            <img
                                src={images.story.chef}
                                alt="Chef plating a signature dish"
                                className="h-full min-h-[280px] w-full object-cover sm:absolute sm:inset-0"
                                loading="lazy"
                            />
                            <div className="absolute bottom-4 left-4 rounded-lg bg-ink/92 px-4 py-3 text-white shadow-lg">
                                <p className="font-display text-2xl font-semibold text-gold-soft">15+</p>
                                <p className="text-[11px] tracking-[0.12em] text-white/75 uppercase">
                                    Years of Experience
                                </p>
                            </div>
                        </div>
                        <div className="overflow-hidden rounded-2xl">
                            <img
                                src={images.story.interior}
                                alt="Restaurant interior"
                                className="h-40 w-full object-cover sm:h-[185px]"
                                loading="lazy"
                            />
                        </div>
                        <div className="overflow-hidden rounded-2xl">
                            <img
                                src={images.story.pizza}
                                alt="Wood-fired pizza"
                                className="h-40 w-full object-cover sm:h-[185px]"
                                loading="lazy"
                            />
                        </div>
                    </div>
                </Reveal>

                <Reveal delay={100}>
                    <div>
                        <p className="font-script text-[1.5rem] leading-none text-ember">Our Story</p>
                        <h2 className="font-display mt-1.5 text-3xl font-semibold text-ink md:text-4xl">
                            A Passion for Great Food
                        </h2>
                        <p className="mt-4 text-[13px] leading-6 text-muted sm:text-sm sm:leading-7">
                            Bynnas Restora began with a simple belief: great meals create lasting
                            memories. From our kitchen to your table, we blend tradition with modern
                            craft to deliver dishes that feel both familiar and exciting.
                        </p>
                        <p className="mt-3 text-[13px] leading-6 text-muted sm:text-sm sm:leading-7">
                            Every plate is prepared with care, every guest is welcomed like family,
                            and every evening is designed around comfort, flavor, and hospitality.
                        </p>

                        <ul className="mt-5 space-y-2.5">
                            {storyPoints.map((point) => (
                                <li key={point} className="flex items-start gap-3 text-sm text-ink">
                                    <span className="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-ember text-white">
                                        <Check className="h-3 w-3" strokeWidth={3} />
                                    </span>
                                    <span className="leading-6">{point}</span>
                                </li>
                            ))}
                        </ul>

                        <div className="mt-6 flex flex-wrap items-end justify-between gap-4 border-t border-line pt-5">
                            <div>
                                <p className="font-script text-3xl text-ember">Chef Bynnas</p>
                                <p className="mt-0.5 text-sm text-muted">Founder & Head Chef</p>
                            </div>
                            <div className="flex items-center gap-3 rounded-lg bg-ink px-4 py-3 text-white shadow-lg">
                                <span className="flex h-9 w-9 items-center justify-center rounded-full bg-ember/20 text-gold-soft">
                                    <Trophy className="h-4 w-4" />
                                </span>
                                <div>
                                    <p className="font-display text-xl font-semibold text-gold-soft">
                                        25K+
                                    </p>
                                    <p className="text-[11px] text-white/70">Happy Customers</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </Reveal>
            </div>
        </section>
    );
}
