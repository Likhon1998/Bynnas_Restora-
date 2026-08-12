export default function SectionHeading({
    eyebrow,
    title,
    description,
    align = 'left',
    light = false,
}) {
    const alignClass =
        align === 'center' ? 'mx-auto text-center items-center' : 'items-start text-left';

    return (
        <div className={`flex max-w-2xl flex-col ${alignClass}`}>
            {eyebrow && (
                <p
                    className={`font-script text-[1.45rem] leading-none ${
                        light ? 'text-gold-soft' : 'text-ember'
                    }`}
                >
                    {eyebrow}
                </p>
            )}
            <h2
                className={`font-display mt-1.5 text-3xl font-semibold tracking-tight md:text-4xl ${
                    light ? 'text-white' : 'text-ink'
                }`}
            >
                {title}
            </h2>
            {description && (
                <p
                    className={`mt-3 text-[13px] leading-6 md:text-sm ${
                        light ? 'text-white/65' : 'text-muted'
                    }`}
                >
                    {description}
                </p>
            )}
        </div>
    );
}
