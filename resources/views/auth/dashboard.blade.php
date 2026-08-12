<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard — Bynnas Restora</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600&family=DM+Sans:opsz,wght@9..40,400;9..40,600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        body { margin: 0; font-family: 'DM Sans', sans-serif; background: #f4f2ee; color: #0f172a; }
        .wrap { min-height: 100dvh; display: grid; place-items: center; padding: 1.5rem; }
        .card { width: min(100%, 440px); background: #fff; border: 1px solid #ebe4d8; border-radius: 1rem; padding: 1.75rem; box-shadow: 0 16px 40px rgba(15,23,42,.08); }
        h1 { font-family: 'Cormorant Garamond', Georgia, serif; font-size: 2rem; margin: 0 0 .35rem; }
        p { margin: 0; color: #6f695f; font-size: .92rem; line-height: 1.5; }
        .actions { margin-top: 1.25rem; display: flex; gap: .75rem; flex-wrap: wrap; }
        .btn { display: inline-flex; align-items: center; justify-content: center; height: 2.6rem; padding: 0 1rem; border-radius: .5rem; font-size: .9rem; font-weight: 600; text-decoration: none; border: 0; cursor: pointer; }
        .btn-primary { background: #0f172a; color: #fff; }
        .btn-ghost { background: transparent; color: #c47a25; border: 1.5px solid #c47a25; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <h1>Welcome, {{ $user->name }}</h1>
        <p>You’re signed in as <strong>{{ $user->email }}</strong>. The Filament admin panel will live at <code>/admin</code> next.</p>
        <div class="actions">
            <a class="btn btn-primary" href="{{ url('/') }}">View Website</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-ghost" type="submit">Sign Out</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
