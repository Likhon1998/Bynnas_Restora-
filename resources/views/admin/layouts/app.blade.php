<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — Bynnas Restora</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet">
    @vite(['resources/css/admin.css'])
</head>
<body>
@php
    $user = $user ?? auth()->user();
    $nav = $nav ?? \App\Support\AdminNav::withActive($activeNav ?? 'dashboard');
    $icons = $icons ?? \App\Support\AdminNav::icons();
@endphp

<div class="overlay" id="overlay"></div>

<div class="shell">
    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <div class="brand-mark">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 13c0-3.5 2-6.5 6-8 4 1.5 6 4.5 6 8"/><path d="M6 13h12v2a4 4 0 0 1-4 4H10a4 4 0 0 1-4-4v-2Z"/><path d="M10 19v2"/><path d="M14 19v2"/>
                </svg>
            </div>
            <div>
                <div class="brand-name">Bynnas <em>Restora</em></div>
                <div class="brand-sub">Restaurant Management</div>
            </div>
        </div>

        <nav class="nav">
            @foreach ($nav as $group)
                @if ($group['section'])
                    <div class="nav-label">{{ $group['section'] }}</div>
                @endif
                @foreach ($group['items'] as $item)
                    @if (!empty($item['route']))
                        <a href="{{ route($item['route']) }}"
                           class="nav-item {{ !empty($item['active']) ? 'active' : '' }}"
                           @if(!empty($item['target'])) target="{{ $item['target'] }}" rel="noopener" @endif>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $icons[$item['icon']] !!}</svg>
                            <span>{{ $item['label'] }}</span>
                            @isset($item['badge'])@if($item['badge'] !== null)<span class="nav-badge">{{ $item['badge'] }}</span>@endif @endisset
                        </a>
                    @else
                        <button type="button" class="nav-item {{ !empty($item['active']) ? 'active' : '' }}" disabled title="Coming soon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $icons[$item['icon']] !!}</svg>
                            <span>{{ $item['label'] }}</span>
                            @isset($item['badge'])<span class="nav-badge">{{ $item['badge'] }}</span>@endisset
                        </button>
                    @endif
                @endforeach
            @endforeach
        </nav>
    </aside>

    <div class="main">
        <header class="topbar">
            <button type="button" class="menu-btn" id="menuBtn" aria-label="Toggle sidebar" aria-controls="sidebar" aria-expanded="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>

            <label class="search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="m20 20-3-3"/></svg>
                <input type="search" placeholder="Search orders, menu items, customers...">
                <kbd>Ctrl + K</kbd>
            </label>

            <div class="top-actions">
                <a href="{{ route('admin.pos.index') }}" target="_blank" rel="noopener" class="btn btn-green">+ New Order</a>
                <a href="{{ route('admin.reservations.create') }}" class="btn btn-blue">Reservation</a>
                <a href="{{ route('admin.pos.index') }}?type=walkin" target="_blank" rel="noopener" class="btn btn-orange">Walk-in</a>
                <a href="{{ route('admin.tables.index') }}#qr-codes" class="btn btn-purple">QR Order</a>

                <button type="button" class="icon-btn" aria-label="Notifications">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                    <span class="badge">5</span>
                </button>
                <button type="button" class="icon-btn" aria-label="Messages">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 15a4 4 0 0 1-4 4H7l-4 4V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/></svg>
                    <span class="badge">2</span>
                </button>

                <div class="user-menu" id="userMenu">
                    <button type="button" class="user-chip" id="userMenuBtn" aria-haspopup="true" aria-expanded="false">
                        <div class="user-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                        <div class="user-meta">
                            <small>Welcome back,</small>
                            <strong>{{ $user->name }}</strong>
                        </div>
                        <svg class="user-caret" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                    </button>

                    <div class="user-dropdown" id="userDropdown" role="menu">
                        <div class="user-dropdown-head">
                            <div class="user-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                            <div>
                                <strong>{{ $user->name }}</strong>
                                <small>{{ $user->email }}</small>
                            </div>
                        </div>
                        <a href="{{ route('profile.edit') }}" class="user-dropdown-item" role="menuitem">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            Profile settings
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="user-dropdown-item danger" role="menuitem">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                                Log out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <div class="content">
            @if (session('success'))
                <div class="flash success">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="flash danger">
                    {{ $errors->first() }}
                </div>
            @endif

            @yield('content')
        </div>

        <footer class="footer">
            <div>© {{ date('Y') }} Bynnas Restora. All rights reserved.</div>
            <nav>
                <a href="#">Privacy Policy</a>
                <a href="#">Terms & Conditions</a>
                <a href="#">Support</a>
            </nav>
        </footer>
    </div>
</div>

<script>
(function () {
    var btn = document.getElementById('menuBtn');
    var side = document.getElementById('sidebar');
    var overlay = document.getElementById('overlay');
    var shell = document.querySelector('.shell');
    var storageKey = 'bynnas.admin.sidebarCollapsed';
    var mq = window.matchMedia('(max-width: 1100px)');

    function isMobile() {
        return mq.matches;
    }

    function setExpandedAttr(expanded) {
        if (btn) btn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
    }

    function closeMobile() {
        if (!side || !overlay) return;
        side.classList.remove('open');
        overlay.classList.remove('open');
        setExpandedAttr(false);
    }

    function openMobile() {
        if (!side || !overlay) return;
        side.classList.add('open');
        overlay.classList.add('open');
        setExpandedAttr(true);
    }

    function applyDesktopCollapse(collapsed) {
        if (!shell) return;
        shell.classList.toggle('sidebar-collapsed', !!collapsed);
        try { localStorage.setItem(storageKey, collapsed ? '1' : '0'); } catch (e) {}
        setExpandedAttr(!collapsed);
    }

    function syncForViewport() {
        if (isMobile()) {
            if (shell) shell.classList.remove('sidebar-collapsed');
            closeMobile();
        } else {
            if (side) side.classList.remove('open');
            if (overlay) overlay.classList.remove('open');
            var saved = false;
            try { saved = localStorage.getItem(storageKey) === '1'; } catch (e) {}
            applyDesktopCollapse(saved);
        }
    }

    if (btn && side && shell) {
        btn.addEventListener('click', function () {
            if (isMobile()) {
                side.classList.contains('open') ? closeMobile() : openMobile();
                return;
            }
            applyDesktopCollapse(!shell.classList.contains('sidebar-collapsed'));
        });
    }
    if (overlay) overlay.addEventListener('click', closeMobile);

    if (mq.addEventListener) {
        mq.addEventListener('change', syncForViewport);
    } else if (mq.addListener) {
        mq.addListener(syncForViewport);
    }
    syncForViewport();

    var userMenu = document.getElementById('userMenu');
    var userBtn = document.getElementById('userMenuBtn');
    if (userMenu && userBtn) {
        userBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            var openMenu = userMenu.classList.toggle('open');
            userBtn.setAttribute('aria-expanded', openMenu ? 'true' : 'false');
        });
        document.addEventListener('click', function (e) {
            if (!userMenu.contains(e.target)) {
                userMenu.classList.remove('open');
                userBtn.setAttribute('aria-expanded', 'false');
            }
        });
    }
})();
</script>
@stack('scripts')
</body>
</html>
