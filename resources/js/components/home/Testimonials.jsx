import { useState } from 'react';
import { ChevronLeft, ChevronRight, Quote } from 'lucide-react';
import { images } from '../../data/homeStatic';
import Reveal from '../ui/Reveal';
import SectionHeading from '../ui/SectionHeading';

export default function Testimonials() {
    const [index, setIndex] = useState(0);
    const items = images.testimonials;

    const prev = () => setIndex((i) => (i === 0 ? items.length - 1 : i - 1));
    const next = () => setIndex((i) => (i === items.length - 1 ? 0 : i + 1));

    const visible = [
        items[index],
        items[(index + 1) % items.length],
        items[(index + 2) % items.length],
    ];

    return (
        <section className="section-pad bg-paper">
            <div className="site-container">
                <Reveal>
                    <SectionHeading
                        align="center"
                        eyebrow="Testimonials"
                        title="What Our Customers Say"
                    />
                </Reveal>

                <div className="relative mt-7 lg:mt-8">
                    <button
                        type="button"
                        onClick={prev}
                        aria-label="Previous testimonial"
                        className="absolute top-1/2 -left-2 z-10 hidden h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-white text-ink shadow-md ring-1 ring-line transition hover:text-ember md:flex lg:-left-5"
                    >
                        <ChevronLeft className="h-5 w-5" />
                    </button>
                    <button
                        type="button"
                        onClick={next}
                        aria-label="Next testimonial"
                        className="absolute top-1/2 -right-2 z-10 hidden h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-ember text-white shadow-md transition hover:bg-ember-deep md:flex lg:-right-5"
                    >
                        <ChevronRight className="h-5 w-5" />
                    </button>

                    <div className="grid gap-5 md:grid-cols-3 md:gap-6">
                        {visible.map((item, i) => (
                            <Reveal key={`${item.id}-${index}`} delay={i * 90}>
                                <article className="testimonial-card flex flex-col p-6">
                                    <Quote className="h-8 w-8 fill-ember/20 text-ember" />
                                    <p className="font-display mt-4 flex-1 text-[1.05rem] leading-7 text-ink/80 italic">
                                        “{item.quote}”
                                    </p>
                                    <div className="mt-6 flex items-center gap-3 border-t border-line pt-5">
                                        <img
                                            src={item.avatar}
                                            alt={item.name}
                                            className="h-12 w-12 rounded-full object-cover"
                                            loading="lazy"
                                        />
                                        <div>
                                            <p className="font-semibold text-ink">{item.name}</p>
                                            <p className="text-xs text-muted">{item.role}</p>
                                        </div>
                                    </div>
                                </article>
                            </Reveal>
                        ))}
                    </div>

                    <div className="mt-7 flex justify-center gap-3 md:hidden">
                        <button
                            type="button"
                            onClick={prev}
                            className="flex h-10 w-10 items-center justify-center rounded-full bg-white ring-1 ring-line"
                            aria-label="Previous"
                        >
                            <ChevronLeft className="h-5 w-5" />
                        </button>
                        <button
                            type="button"
                            onClick={next}
                            className="flex h-10 w-10 items-center justify-center rounded-full bg-ember text-white"
                            aria-label="Next"
                        >
                            <ChevronRight className="h-5 w-5" />
                        </button>
                    </div>
                </div>
            </div>
        </section>
    );
}
