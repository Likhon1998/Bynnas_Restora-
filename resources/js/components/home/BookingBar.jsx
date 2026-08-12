import { ArrowRight, CalendarDays, Clock3, Users } from 'lucide-react';
import Reveal from '../ui/Reveal';

export default function BookingBar() {
    return (
        <section id="reservations" className="relative z-20 -mt-10 sm:-mt-12">
            <div className="site-container">
                <Reveal>
                    <div className="booking-card grid gap-4 p-4 sm:gap-5 sm:p-5 lg:grid-cols-[220px_1fr_1fr_1fr_auto] lg:items-end lg:gap-4">
                        <div>
                            <h2 className="font-display text-[1.65rem] leading-tight font-semibold text-ink sm:text-[1.85rem]">
                                Book Your Table
                            </h2>
                            <p className="mt-1.5 text-[12px] leading-5 text-muted">
                                Reserve your table for a great dining experience.
                            </p>
                        </div>

                        <label className="block min-w-0">
                            <span className="mb-1.5 flex items-center gap-2 text-xs font-medium text-ember">
                                <CalendarDays className="h-3.5 w-3.5" />
                                Select Date
                            </span>
                            <input type="date" className="field" />
                        </label>

                        <label className="block min-w-0">
                            <span className="mb-1.5 flex items-center gap-2 text-xs font-medium text-ember">
                                <Clock3 className="h-3.5 w-3.5" />
                                Select Time
                            </span>
                            <select className="field">
                                <option>7:00 PM</option>
                                <option>7:30 PM</option>
                                <option>8:00 PM</option>
                                <option>8:30 PM</option>
                                <option>9:00 PM</option>
                            </select>
                        </label>

                        <label className="block min-w-0">
                            <span className="mb-1.5 flex items-center gap-2 text-xs font-medium text-ember">
                                <Users className="h-3.5 w-3.5" />
                                No. of People
                            </span>
                            <select className="field">
                                <option>2 People</option>
                                <option>3 People</option>
                                <option>4 People</option>
                                <option>5 People</option>
                                <option>6+ People</option>
                            </select>
                        </label>

                        <button
                            type="button"
                            className="btn-primary h-[42px] w-full whitespace-nowrap sm:w-auto lg:self-end"
                        >
                            Find a Table
                            <ArrowRight className="h-4 w-4" />
                        </button>
                    </div>
                </Reveal>
            </div>
        </section>
    );
}
