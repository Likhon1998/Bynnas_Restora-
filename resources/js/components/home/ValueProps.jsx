import { CalendarCheck2, Home, Salad, ShieldCheck } from 'lucide-react';
import { valueProps } from '../../data/homeStatic';
import Reveal from '../ui/Reveal';

const icons = {
    calendar: CalendarCheck2,
    salad: Salad,
    home: Home,
    shield: ShieldCheck,
};

export default function ValueProps() {
    return (
        <section className="pt-10 pb-1 sm:pt-12 lg:pt-14">
            <div className="site-container grid gap-6 sm:grid-cols-2 lg:grid-cols-4 lg:gap-5">
                {valueProps.map((item, index) => {
                    const Icon = icons[item.key];
                    return (
                        <Reveal key={item.title} delay={index * 60}>
                            <div className="text-center">
                                <div className="feature-icon">
                                    <Icon className="h-6 w-6" strokeWidth={1.6} />
                                </div>
                                <h3 className="font-display mt-3.5 text-[1.25rem] font-semibold text-ink">
                                    {item.title}
                                </h3>
                                <p className="mt-1.5 text-[13px] leading-relaxed text-muted">
                                    {item.text}
                                </p>
                            </div>
                        </Reveal>
                    );
                })}
            </div>
        </section>
    );
}
