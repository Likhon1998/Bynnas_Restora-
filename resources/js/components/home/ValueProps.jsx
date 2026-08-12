import { CalendarCheck2, Home, Salad, ShieldCheck } from 'lucide-react';
import { getSiteSettings } from '../../data/siteSettings';
import Reveal from '../ui/Reveal';

const icons = {
    calendar: CalendarCheck2,
    salad: Salad,
    home: Home,
    shield: ShieldCheck,
};

export default function ValueProps() {
    const settings = getSiteSettings();
    const items = settings.value_props?.length ? settings.value_props : [];

    return (
        <section className="pt-12 pb-2 sm:pt-14 lg:pt-16">
            <div className="site-container grid gap-8 sm:grid-cols-2 lg:grid-cols-4 lg:gap-6">
                {items.map((item, index) => {
                    const Icon = icons[item.key] || ShieldCheck;
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
