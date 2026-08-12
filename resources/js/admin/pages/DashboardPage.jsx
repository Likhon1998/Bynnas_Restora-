import {
    crmStats,
    financialSnapshot,
    floorTables,
    inventoryAlerts,
    kpis,
    liveOrders,
    purchaseOrders,
    recentActivities,
    revenueWeek,
    staffPerformance,
    tableLegend,
    tableStatusColor,
    topSelling,
    upcomingReservations,
} from '../data/dashboardStatic';

const toneBg = {
    orange: 'from-orange-500/20 to-orange-500/5 text-orange-300',
    purple: 'from-purple-500/20 to-purple-500/5 text-purple-300',
    blue: 'from-blue-500/20 to-blue-500/5 text-blue-300',
    green: 'from-emerald-500/20 to-emerald-500/5 text-emerald-300',
    amber: 'from-amber-500/20 to-amber-500/5 text-amber-300',
};

const badgeTone = {
    amber: 'bg-amber-500/15 text-amber-300 ring-amber-500/30',
    green: 'bg-emerald-500/15 text-emerald-300 ring-emerald-500/30',
    blue: 'bg-blue-500/15 text-blue-300 ring-blue-500/30',
    red: 'bg-red-500/15 text-red-300 ring-red-500/30',
    slate: 'bg-slate-500/15 text-slate-300 ring-slate-500/30',
    purple: 'bg-purple-500/15 text-purple-300 ring-purple-500/30',
    orange: 'bg-orange-500/15 text-orange-300 ring-orange-500/30',
};

function Card({ title, action, children, className = '' }) {
    return (
        <section
            className={`rounded-2xl border border-white/8 bg-[#121722] p-4 shadow-[0_10px_30px_rgba(0,0,0,0.2)] ${className}`}
        >
            {(title || action) && (
                <div className="mb-3 flex items-center justify-between gap-2">
                    {title ? (
                        <h3 className="text-sm font-semibold tracking-wide text-white/90">
                            {title}
                        </h3>
                    ) : (
                        <span />
                    )}
                    {action}
                </div>
            )}
            {children}
        </section>
    );
}

function Sparkline({ values, color = '#c47a25' }) {
    const max = Math.max(...values);
    const min = Math.min(...values);
    const w = 88;
    const h = 28;
    const points = values
        .map((v, i) => {
            const x = (i / (values.length - 1)) * w;
            const y = h - ((v - min) / (max - min || 1)) * (h - 4) - 2;
            return `${x},${y}`;
        })
        .join(' ');

    return (
        <svg width={w} height={h} viewBox={`0 0 ${w} ${h}`} className="overflow-visible">
            <polyline
                fill="none"
                stroke={color}
                strokeWidth="2"
                strokeLinecap="round"
                strokeLinejoin="round"
                points={points}
            />
        </svg>
    );
}

function RevenueChart({ data }) {
    const max = Math.max(...data.map((d) => d.value));
    const w = 520;
    const h = 180;
    const pad = 24;
    const points = data.map((d, i) => {
        const x = pad + (i / (data.length - 1)) * (w - pad * 2);
        const y = h - pad - (d.value / max) * (h - pad * 2);
        return { ...d, x, y };
    });
    const path = points.map((p, i) => `${i === 0 ? 'M' : 'L'}${p.x},${p.y}`).join(' ');
    const area = `${path} L${points[points.length - 1].x},${h - pad} L${points[0].x},${h - pad} Z`;
    const peak = points.reduce((a, b) => (a.value > b.value ? a : b));

    return (
        <div className="relative">
            <svg viewBox={`0 0 ${w} ${h}`} className="h-48 w-full">
                <defs>
                    <linearGradient id="revFill" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stopColor="#c47a25" stopOpacity="0.35" />
                        <stop offset="100%" stopColor="#c47a25" stopOpacity="0" />
                    </linearGradient>
                </defs>
                {[0, 1, 2, 3].map((i) => (
                    <line
                        key={i}
                        x1={pad}
                        x2={w - pad}
                        y1={pad + i * ((h - pad * 2) / 3)}
                        y2={pad + i * ((h - pad * 2) / 3)}
                        stroke="rgba(255,255,255,0.06)"
                    />
                ))}
                <path d={area} fill="url(#revFill)" />
                <path
                    d={path}
                    fill="none"
                    stroke="#c47a25"
                    strokeWidth="2.5"
                    strokeLinecap="round"
                />
                {points.map((p) => (
                    <circle key={p.day} cx={p.x} cy={p.y} r="3.5" fill="#e0a84a" />
                ))}
                {points.map((p) => (
                    <text
                        key={`${p.day}-label`}
                        x={p.x}
                        y={h - 6}
                        textAnchor="middle"
                        fill="rgba(255,255,255,0.4)"
                        fontSize="11"
                    >
                        {p.day}
                    </text>
                ))}
            </svg>
            <div
                className="absolute rounded-lg border border-[#c47a25]/40 bg-[#0b0e14]/95 px-2.5 py-1.5 text-[11px] shadow-lg"
                style={{
                    left: `calc(${(peak.x / w) * 100}% - 48px)`,
                    top: `${(peak.y / h) * 100 - 8}%`,
                }}
            >
                <p className="text-white/50">{peak.day}</p>
                <p className="font-semibold text-[#e0a84a]">
                    ৳ {peak.value.toLocaleString()}.30
                </p>
            </div>
        </div>
    );
}

function Ring({ pct, label, value, change }) {
    const r = 28;
    const c = 2 * Math.PI * r;
    const offset = c - (pct / 100) * c;

    return (
        <div className="flex flex-col items-center text-center">
            <svg width="76" height="76" viewBox="0 0 76 76">
                <circle cx="38" cy="38" r={r} stroke="rgba(255,255,255,0.08)" strokeWidth="7" fill="none" />
                <circle
                    cx="38"
                    cy="38"
                    r={r}
                    stroke="#c47a25"
                    strokeWidth="7"
                    fill="none"
                    strokeLinecap="round"
                    strokeDasharray={c}
                    strokeDashoffset={offset}
                    transform="rotate(-90 38 38)"
                />
                <text
                    x="38"
                    y="41"
                    textAnchor="middle"
                    fill="#fff"
                    fontSize="12"
                    fontWeight="600"
                >
                    {pct}%
                </text>
            </svg>
            <p className="mt-1.5 text-[11px] text-white/45">{label}</p>
            <p className="text-[12px] font-semibold text-white">{value}</p>
            {change ? (
                <p className="text-[10px] text-emerald-400">{change}</p>
            ) : null}
        </div>
    );
}

export default function DashboardPage({ firstName }) {
    return (
        <div className="space-y-5">
            <div className="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h1 className="font-display text-2xl font-semibold text-white md:text-[1.85rem]">
                        Good morning, {firstName}!
                    </h1>
                    <p className="mt-1 text-sm text-white/45">
                        Here’s what’s happening at Bynnas Restora today.
                    </p>
                </div>
                <div className="flex items-center gap-2">
                    <button
                        type="button"
                        className="rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm text-white/80"
                    >
                        May 20, 2026
                    </button>
                    <button
                        type="button"
                        className="rounded-xl px-3 py-2 text-sm font-semibold text-white"
                        style={{ background: '#c47a25' }}
                    >
                        End of Day
                    </button>
                </div>
            </div>

            {/* KPIs */}
            <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
                {kpis.map((kpi) => (
                    <Card key={kpi.title} className="!p-3.5">
                        <div className="flex items-start justify-between gap-2">
                            <div>
                                <p className="text-[11px] text-white/45">{kpi.title}</p>
                                <p className="mt-1 text-lg font-semibold tracking-tight text-white">
                                    {kpi.value}
                                </p>
                                <p className="mt-1 text-[11px] font-medium text-emerald-400">
                                    {kpi.change}{' '}
                                    <span className="text-white/35">vs yesterday</span>
                                </p>
                            </div>
                            <div
                                className={`rounded-xl bg-gradient-to-br p-2 ${toneBg[kpi.tone]}`}
                            >
                                <Sparkline values={kpi.spark} />
                            </div>
                        </div>
                    </Card>
                ))}
            </div>

            {/* Floor / Orders / Revenue */}
            <div className="grid gap-4 xl:grid-cols-[1.2fr_0.9fr_1.1fr]">
                <Card title="Table / Floor Overview">
                    <div className="grid gap-4 md:grid-cols-[140px_1fr]">
                        <ul className="space-y-2">
                            {tableLegend.map((item) => (
                                <li
                                    key={item.label}
                                    className="flex items-center justify-between gap-2 text-[12px]"
                                >
                                    <span className="flex items-center gap-2 text-white/70">
                                        <span
                                            className="h-2.5 w-2.5 rounded-full"
                                            style={{ background: item.color }}
                                        />
                                        {item.label}
                                    </span>
                                    <span className="font-semibold text-white/90">{item.count}</span>
                                </li>
                            ))}
                        </ul>
                        <div className="grid grid-cols-4 gap-2 rounded-xl border border-white/8 bg-[#0b0e14]/60 p-3">
                            {floorTables.map((t) => (
                                <div
                                    key={t.id}
                                    className="flex aspect-square items-center justify-center rounded-lg text-xs font-semibold text-white shadow-inner"
                                    style={{ background: tableStatusColor[t.status] }}
                                    title={`Table ${t.id} · ${t.status}`}
                                >
                                    {String(t.id).padStart(2, '0')}
                                </div>
                            ))}
                        </div>
                    </div>
                    <div className="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-4">
                        {[
                            ['Seated', '12 Tables'],
                            ['Occupied', '32/48'],
                            ['Waiting', '2 Orders'],
                            ['Turnover', '2.3x'],
                        ].map(([k, v]) => (
                            <div
                                key={k}
                                className="rounded-lg border border-white/8 bg-white/3 px-2.5 py-2"
                            >
                                <p className="text-[10px] text-white/40">{k}</p>
                                <p className="text-[12px] font-semibold">{v}</p>
                            </div>
                        ))}
                    </div>
                </Card>

                <Card title="Live Order Queue">
                    <ul className="space-y-2.5">
                        {liveOrders.map((o) => (
                            <li
                                key={o.id}
                                className="flex items-center justify-between gap-2 rounded-xl border border-white/8 bg-white/[0.02] px-3 py-2.5"
                            >
                                <div>
                                    <p className="text-[13px] font-semibold">{o.id}</p>
                                    <p className="text-[11px] text-white/40">
                                        {o.source} · {o.ago} ago
                                    </p>
                                </div>
                                <span
                                    className={`rounded-full px-2 py-0.5 text-[10px] font-semibold ring-1 ${badgeTone[o.tone]}`}
                                >
                                    {o.status}
                                </span>
                            </li>
                        ))}
                    </ul>
                </Card>

                <Card title="Revenue Overview">
                    <RevenueChart data={revenueWeek} />
                </Card>
            </div>

            {/* Inventory / PO / Finance / Top selling */}
            <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <Card title="Inventory Alert">
                    <ul className="space-y-3">
                        {inventoryAlerts.map((item) => (
                            <li key={item.name}>
                                <div className="mb-1 flex items-center justify-between text-[12px]">
                                    <span className="text-white/80">{item.name}</span>
                                    <span className="text-white/45">{item.left}</span>
                                </div>
                                <div className="h-1.5 overflow-hidden rounded-full bg-white/8">
                                    <div
                                        className={`h-full rounded-full ${
                                            item.tone === 'red' ? 'bg-red-500' : 'bg-amber-400'
                                        }`}
                                        style={{ width: `${item.pct}%` }}
                                    />
                                </div>
                            </li>
                        ))}
                    </ul>
                </Card>

                <Card title="Purchase Orders">
                    <ul className="space-y-2.5">
                        {purchaseOrders.map((po) => (
                            <li
                                key={po.id}
                                className="rounded-xl border border-white/8 px-3 py-2.5"
                            >
                                <div className="flex items-center justify-between gap-2">
                                    <p className="text-[13px] font-semibold">{po.id}</p>
                                    <span
                                        className={`rounded-full px-2 py-0.5 text-[10px] font-semibold ring-1 ${badgeTone[po.tone]}`}
                                    >
                                        {po.status}
                                    </span>
                                </div>
                                <p className="mt-1 text-[11px] text-white/45">
                                    {po.supplier} · {po.date}
                                </p>
                            </li>
                        ))}
                    </ul>
                </Card>

                <Card title="Financial Snapshot">
                    <ul className="space-y-2.5">
                        {financialSnapshot.map((row) => (
                            <li
                                key={row.label}
                                className="flex items-center justify-between gap-2 border-b border-white/6 pb-2 last:border-0 last:pb-0"
                            >
                                <span className="text-[12px] text-white/55">{row.label}</span>
                                <span
                                    className={`text-right text-[12px] font-semibold ${
                                        row.highlight ? 'text-[#e0a84a]' : 'text-white'
                                    }`}
                                >
                                    {row.value}
                                    {row.meta ? (
                                        <span className="ml-1 text-[10px] text-emerald-400">
                                            {row.meta}
                                        </span>
                                    ) : null}
                                </span>
                            </li>
                        ))}
                    </ul>
                </Card>

                <Card title="Top Selling Items">
                    <ul className="space-y-2.5">
                        {topSelling.map((item) => (
                            <li key={item.name} className="flex items-center gap-2.5">
                                <img
                                    src={item.image}
                                    alt=""
                                    className="h-10 w-10 rounded-lg object-cover"
                                />
                                <div className="min-w-0 flex-1">
                                    <p className="truncate text-[12px] font-semibold">
                                        {item.name}
                                    </p>
                                    <p className="text-[11px] text-white/40">{item.sold} sold</p>
                                </div>
                                <p className="text-[12px] font-semibold text-[#e0a84a]">
                                    {item.revenue}
                                </p>
                            </li>
                        ))}
                    </ul>
                </Card>
            </div>

            {/* Bottom row */}
            <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <Card title="Upcoming Reservations">
                    <ul className="space-y-2.5">
                        {upcomingReservations.map((r) => (
                            <li
                                key={`${r.time}-${r.name}`}
                                className="flex items-center justify-between gap-2 rounded-xl border border-white/8 px-3 py-2"
                            >
                                <div>
                                    <p className="text-[12px] font-semibold">{r.name}</p>
                                    <p className="text-[11px] text-white/40">
                                        {r.time} · {r.guests} guests
                                    </p>
                                </div>
                                <span
                                    className={`rounded-full px-2 py-0.5 text-[10px] font-semibold ring-1 ${badgeTone[r.tone]}`}
                                >
                                    {r.status}
                                </span>
                            </li>
                        ))}
                    </ul>
                </Card>

                <Card title="Today’s Staff Performance">
                    <div className="grid grid-cols-2 gap-3">
                        {staffPerformance.map((s) => (
                            <Ring
                                key={s.label}
                                pct={s.pct}
                                label={s.label}
                                value={s.value}
                                change={s.change}
                            />
                        ))}
                    </div>
                </Card>

                <Card title="CRM & Loyalty Overview">
                    <div className="space-y-3">
                        {crmStats.map((s) => (
                            <div
                                key={s.label}
                                className="rounded-xl border border-white/8 bg-white/[0.02] px-3 py-3"
                            >
                                <p className="text-[11px] text-white/45">{s.label}</p>
                                <p className="mt-1 text-xl font-semibold text-[#e0a84a]">
                                    {s.value}
                                </p>
                            </div>
                        ))}
                    </div>
                </Card>

                <Card title="Recent Activities">
                    <ul className="space-y-3">
                        {recentActivities.map((a) => (
                            <li key={a.text} className="flex gap-2.5">
                                <span
                                    className={`mt-1.5 h-2 w-2 shrink-0 rounded-full ${
                                        a.tone === 'green'
                                            ? 'bg-emerald-400'
                                            : a.tone === 'red'
                                              ? 'bg-red-400'
                                              : a.tone === 'blue'
                                                ? 'bg-blue-400'
                                                : a.tone === 'purple'
                                                  ? 'bg-purple-400'
                                                  : 'bg-orange-400'
                                    }`}
                                />
                                <div>
                                    <p className="text-[12px] text-white/85">{a.text}</p>
                                    <p className="text-[10px] text-white/35">{a.time}</p>
                                </div>
                            </li>
                        ))}
                    </ul>
                </Card>
            </div>
        </div>
    );
}
