<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign In — Bynnas Restora</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,500&family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&family=Great+Vibes&display=swap" rel="stylesheet">

    <style>
        :root {
            --gold: #d4a04a;
            --gold-deep: #b8781f;
            --ink: #0b0b0b;
            --paper: rgba(18, 16, 14, 0.72);
        }

        *, *::before, *::after { box-sizing: border-box; }

        html, body {
            height: 100%;
            margin: 0;
            overflow: hidden;
            font-family: 'DM Sans', ui-sans-serif, system-ui, sans-serif;
            color: #fff;
            background: #0b0b0b;
        }

        .stage {
            position: relative;
            height: 100dvh;
            max-height: 100dvh;
            overflow: hidden;
            display: grid;
            place-items: center;
            padding: clamp(1rem, 3vh, 2rem);
        }

        .stage__bg {
            position: absolute;
            inset: 0;
            background:
                url('https://images.unsplash.com/photo-1559339352-11d035aa65de?auto=format&fit=crop&w=2200&q=90')
                center / cover no-repeat;
            transform: scale(1.08);
            animation: drift 28s ease-in-out infinite alternate;
            filter: saturate(1.08) contrast(1.05);
        }

        .stage__veil {
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse at 50% 40%, rgba(0,0,0,0.25), rgba(0,0,0,0.78) 70%),
                linear-gradient(90deg, rgba(0,0,0,0.72) 0%, rgba(0,0,0,0.35) 48%, rgba(0,0,0,0.7) 100%);
        }

        .stage__grain {
            position: absolute;
            inset: 0;
            opacity: 0.12;
            pointer-events: none;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
            mix-blend-mode: soft-light;
        }

        .topbar {
            position: absolute;
            top: clamp(1rem, 2.8vh, 1.75rem);
            left: clamp(1rem, 3vw, 2rem);
            right: clamp(1rem, 3vw, 2rem);
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            text-decoration: none;
            color: inherit;
        }

        .brand__mark {
            width: 2.6rem;
            height: 2.6rem;
            border-radius: 999px;
            display: grid;
            place-items: center;
            border: 1px solid rgba(212, 160, 74, 0.55);
            background: rgba(0,0,0,0.35);
            color: var(--gold);
            backdrop-filter: blur(8px);
        }

        .brand__mark svg { width: 1.25rem; height: 1.25rem; }

        .brand__text strong {
            display: block;
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: 1.35rem;
            font-weight: 600;
            letter-spacing: 0.02em;
            line-height: 1;
        }

        .brand__text strong span { color: var(--gold); }

        .brand__text small {
            display: block;
            margin-top: 0.2rem;
            font-size: 0.62rem;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.55);
        }

        .topbar__link {
            font-size: 0.8rem;
            color: rgba(255,255,255,0.75);
            text-decoration: none;
            border: 1px solid rgba(255,255,255,0.18);
            border-radius: 999px;
            padding: 0.55rem 1rem;
            background: rgba(255,255,255,0.04);
            backdrop-filter: blur(8px);
            transition: border-color 0.2s ease, color 0.2s ease;
        }

        .topbar__link:hover {
            color: #fff;
            border-color: rgba(212, 160, 74, 0.55);
        }

        .panel {
            position: relative;
            z-index: 2;
            width: min(100%, 980px);
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            gap: 0;
            border-radius: 1.6rem;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.12);
            background: rgba(12, 10, 9, 0.55);
            box-shadow:
                0 40px 100px rgba(0,0,0,0.45),
                0 0 0 1px rgba(212,160,74,0.08) inset;
            backdrop-filter: blur(22px);
            max-height: min(86dvh, 640px);
        }

        .panel__story {
            padding: clamp(1.5rem, 3.5vh, 2.4rem) clamp(1.4rem, 3vw, 2.2rem);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 1.25rem;
            min-height: 0;
            background:
                linear-gradient(160deg, rgba(212,160,74,0.12), transparent 42%),
                rgba(0,0,0,0.18);
            border-right: 1px solid rgba(255,255,255,0.08);
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: var(--gold);
        }

        .eyebrow::before,
        .eyebrow::after {
            content: '';
            width: 1.25rem;
            height: 1px;
            background: rgba(212,160,74,0.7);
        }

        .panel__story h1 {
            margin: 0.85rem 0 0;
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: clamp(2.1rem, 3.5vw, 2.9rem);
            font-weight: 600;
            line-height: 1.05;
        }

        .panel__story h1 em {
            font-style: italic;
            color: var(--gold);
            font-weight: 500;
        }

        .panel__story p {
            margin: 0.9rem 0 0;
            max-width: 22rem;
            font-size: 0.92rem;
            line-height: 1.6;
            color: rgba(255,255,255,0.68);
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.65rem;
        }

        .stat {
            padding: 0.75rem 0.7rem;
            border-radius: 0.9rem;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            text-align: center;
        }

        .stat strong {
            display: block;
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: 1.35rem;
            color: var(--gold);
            line-height: 1;
        }

        .stat span {
            display: block;
            margin-top: 0.3rem;
            font-size: 0.68rem;
            letter-spacing: 0.04em;
            color: rgba(255,255,255,0.55);
            text-transform: uppercase;
        }

        .panel__form {
            padding: clamp(1.4rem, 3.2vh, 2.2rem) clamp(1.3rem, 2.8vw, 2rem);
            background: linear-gradient(180deg, rgba(255,252,247,0.97), rgba(247,241,232,0.95));
            color: #17130f;
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: 0;
        }

        .form-head {
            text-align: center;
            margin-bottom: clamp(0.9rem, 2vh, 1.25rem);
        }

        .form-head__icon {
            width: 3.4rem;
            height: 3.4rem;
            margin: 0 auto 0.75rem;
            border-radius: 1rem;
            display: grid;
            place-items: center;
            color: #fff;
            background: linear-gradient(145deg, #d4a04a, #b8781f);
            box-shadow: 0 12px 28px rgba(184, 120, 31, 0.35);
        }

        .form-head__icon svg { width: 1.45rem; height: 1.45rem; }

        .form-head h2 {
            margin: 0;
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: clamp(1.85rem, 2.8vw, 2.2rem);
            font-weight: 600;
            color: #12100e;
            line-height: 1.1;
        }

        .form-head p {
            margin: 0.35rem 0 0;
            font-size: 0.86rem;
            color: #6d655a;
        }

        .alert {
            margin-bottom: 0.85rem;
            border-radius: 0.75rem;
            border: 1px solid #fecaca;
            background: #fff5f5;
            color: #991b1b;
            font-size: 0.8rem;
            padding: 0.7rem 0.85rem;
        }

        .form {
            display: grid;
            gap: 0.85rem;
        }

        .label {
            display: block;
            margin-bottom: 0.35rem;
            font-size: 0.76rem;
            font-weight: 600;
            color: #3a342c;
        }

        .field { position: relative; }

        .field__icon {
            position: absolute;
            left: 0.9rem;
            top: 50%;
            transform: translateY(-50%);
            width: 1.05rem;
            height: 1.05rem;
            color: #9a8f80;
            pointer-events: none;
        }

        .input {
            width: 100%;
            height: 2.95rem;
            border: 1.5px solid #e5d8c6;
            border-radius: 0.85rem;
            background: #fff;
            padding: 0 2.6rem 0 2.55rem;
            font-size: 0.9rem;
            color: #17130f;
            outline: none;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .input::placeholder { color: #aea293; }

        .input:focus {
            border-color: var(--gold-deep);
            box-shadow: 0 0 0 4px rgba(184, 120, 31, 0.14);
        }

        .input.is-invalid {
            border-color: #dc2626;
            box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.1);
        }

        .field__toggle {
            position: absolute;
            right: 0.4rem;
            top: 50%;
            transform: translateY(-50%);
            width: 2.1rem;
            height: 2.1rem;
            border: 0;
            border-radius: 0.6rem;
            background: transparent;
            color: #9a8f80;
            cursor: pointer;
            display: grid;
            place-items: center;
        }

        .field__toggle:hover {
            color: #17130f;
            background: rgba(0,0,0,0.04);
        }

        .error {
            margin: 0.3rem 0 0;
            font-size: 0.74rem;
            color: #dc2626;
        }

        .row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
        }

        .remember {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            font-size: 0.8rem;
            color: #3a342c;
            cursor: pointer;
            user-select: none;
        }

        .remember input {
            width: 1rem;
            height: 1rem;
            accent-color: var(--gold-deep);
        }

        .forgot {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--gold-deep);
            text-decoration: none;
        }

        .forgot:hover { text-decoration: underline; }

        .submit {
            width: 100%;
            height: 3.05rem;
            border: 0;
            border-radius: 0.9rem;
            background: linear-gradient(135deg, #d4a04a 0%, #b8781f 100%);
            color: #fff;
            font-size: 0.92rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            cursor: pointer;
            box-shadow: 0 14px 30px rgba(184, 120, 31, 0.35);
            transition: filter 0.15s ease, transform 0.15s ease;
        }

        .submit:hover { filter: brightness(1.05); }
        .submit:active { transform: translateY(1px); }
        .submit svg { width: 1rem; height: 1rem; }

        .foot {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0.85rem;
            z-index: 2;
            text-align: center;
            font-size: 0.72rem;
            color: rgba(255,255,255,0.55);
        }

        .foot strong { color: var(--gold); font-weight: 600; }

        @keyframes drift {
            from { transform: scale(1.08) translate3d(0, 0, 0); }
            to { transform: scale(1.14) translate3d(-1.2%, -0.8%, 0); }
        }

        @media (max-width: 860px) {
            .panel {
                grid-template-columns: 1fr;
                width: min(100%, 420px);
                max-height: none;
            }

            .panel__story { display: none; }

            .panel__form {
                padding: 1.4rem 1.25rem 1.5rem;
            }
        }

        @media (max-height: 700px) {
            .panel { max-height: 92dvh; }
            .panel__story, .panel__form { padding: 1.1rem 1.2rem; }
            .form { gap: 0.65rem; }
            .stats { display: none; }
            .form-head__icon { width: 2.8rem; height: 2.8rem; margin-bottom: 0.45rem; }
        }

        @media (prefers-reduced-motion: reduce) {
            .stage__bg { animation: none; }
        }
    </style>
</head>
<body>
<div class="stage">
    <div class="stage__bg" aria-hidden="true"></div>
    <div class="stage__veil" aria-hidden="true"></div>
    <div class="stage__grain" aria-hidden="true"></div>

    <div class="topbar">
        <a class="brand" href="{{ url('/') }}">
            <span class="brand__mark" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 13c0-3.5 2-6.5 6-8 4 1.5 6 4.5 6 8"/>
                    <path d="M6 13h12v2a4 4 0 0 1-4 4H10a4 4 0 0 1-4-4v-2Z"/>
                    <path d="M10 19v2"/><path d="M14 19v2"/>
                </svg>
            </span>
            <span class="brand__text">
                <strong>Bynnas <span>Restora</span></strong>
                <small>Restaurant Management</small>
            </span>
        </a>
        <a class="topbar__link" href="{{ url('/') }}">← Back to website</a>
    </div>

    <div class="panel">
        <section class="panel__story">
            <div>
                <div class="eyebrow">Admin Access</div>
                <h1>Welcome to the<br><em>heart of service.</em></h1>
                <p>Sign in to manage reservations, POS, inventory, and your team — all from one refined workspace.</p>
            </div>

            <div class="stats">
                <div class="stat">
                    <strong>24/7</strong>
                    <span>Operations</span>
                </div>
                <div class="stat">
                    <strong>POS</strong>
                    <span>Fast billing</span>
                </div>
                <div class="stat">
                    <strong>Live</strong>
                    <span>Inventory</span>
                </div>
            </div>
        </section>

        <section class="panel__form">
            <div class="form-head">
                <div class="form-head__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 13c0-3.5 2-6.5 6-8 4 1.5 6 4.5 6 8"/>
                        <path d="M6 13h12v2a4 4 0 0 1-4 4H10a4 4 0 0 1-4-4v-2Z"/>
                        <path d="M10 19v2"/><path d="M14 19v2"/>
                    </svg>
                </div>
                <h2>Welcome Back</h2>
                <p>Sign in to your admin account</p>
            </div>

            @if ($errors->any())
                <div class="alert" role="alert">{{ $errors->first() }}</div>
            @endif

            <form class="form" method="POST" action="{{ route('login') }}" novalidate>
                @csrf

                <div>
                    <label class="label" for="email">Email Address</label>
                    <div class="field">
                        <svg class="field__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect width="20" height="16" x="2" y="4" rx="2"/>
                            <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                        </svg>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email', 'admin@bynnasrestora.com') }}"
                            autocomplete="username"
                            required
                            autofocus
                            placeholder="Enter your email"
                            class="input @error('email') is-invalid @enderror"
                        >
                    </div>
                    @error('email')
                        <p class="error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label" for="password">Password</label>
                    <div class="field">
                        <svg class="field__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            required
                            placeholder="Enter your password"
                            class="input @error('password') is-invalid @enderror"
                        >
                        <button type="button" class="field__toggle" id="toggle-password" aria-label="Show password">
                            <svg id="icon-eye" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg id="icon-eye-off" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" hidden>
                                <path d="M10.7 5.1A10.4 10.4 0 0 1 12 5c6.5 0 10 7 10 7a18.5 18.5 0 0 1-2.2 3.2"/>
                                <path d="M6.6 6.6C3.9 8.4 2 12 2 12s3.5 7 10 7a9.8 9.8 0 0 0 4.4-1"/>
                                <path d="m2 2 20 20"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="row">
                    <label class="remember">
                        <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                        Remember me
                    </label>
                    <a class="forgot" href="{{ route('password.request') }}">Forgot Password?</a>
                </div>

                <button type="submit" class="submit">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                        <polyline points="10 17 15 12 10 7"/>
                        <line x1="15" x2="3" y1="12" y2="12"/>
                    </svg>
                    Sign In
                </button>
            </form>
        </section>
    </div>

    <p class="foot">© {{ date('Y') }} <strong>Bynnas Restora</strong>. All rights reserved.</p>
</div>

<script>
    (function () {
        var input = document.getElementById('password');
        var btn = document.getElementById('toggle-password');
        var eye = document.getElementById('icon-eye');
        var eyeOff = document.getElementById('icon-eye-off');
        if (!input || !btn) return;

        btn.addEventListener('click', function () {
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            eye.hidden = show;
            eyeOff.hidden = !show;
            btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        });
    })();
</script>
</body>
</html>
