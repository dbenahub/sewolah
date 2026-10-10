@php
    $adminCss = public_path('css/admin.css');
    $adminCssV = file_exists($adminCss) ? filemtime($adminCss) : '1';
    $pendingCount = \App\Models\Lead::where('status', 'baru')->count();
    $user = auth()->user();
    $icons = [
        'dashboard' => '<path d="M4 13h6V4H4v9Zm0 7h6v-5H4v5Zm10 0h6v-9h-6v9Zm0-16v5h6V4h-6Z"/>',
        'bookings' => '<path d="M8 4h8M8 4a2 2 0 0 0-2 2v0H5a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1V7a1 1 0 0 0-1-1h-1v0a2 2 0 0 0-2-2M8 4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2M8 12h8M8 16h5"/>',
        'vehicles' => '<path d="M5 16.5V12l1.8-4.6A2 2 0 0 1 8.7 6h6.6a2 2 0 0 1 1.9 1.4L19 12v4.5M5 16.5h14M5 16.5V18a1 1 0 0 0 1 1h1a1 1 0 0 0 1-1v-1.5m8 0V18a1 1 0 0 0 1 1h1a1 1 0 0 0 1-1v-1.5M5 12h14M7.5 14.2h1M15.5 14.2h1"/>',
        'pixels' => '<path d="M12 3v3M12 18v3M3 12h3M18 12h3M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/><path d="M12 19a7 7 0 1 0 0-14 7 7 0 0 0 0 14Z"/>',
        'page' => '<path d="M4 5a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5ZM4 9h16M9 9v11"/>',
        'password' => '<path d="M7 11V8a5 5 0 0 1 10 0v3M6 11h12a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1v-7a1 1 0 0 1 1-1Zm6 4v2"/>',
        'logout' => '<path d="M15 4h3a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1h-3M10 16l-4-4 4-4M6 12h10"/>',
        'site' => '<path d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18ZM3.6 9h16.8M3.6 15h16.8M12 3c2.2 2.4 3.3 5.4 3.3 9s-1.1 6.6-3.3 9c-2.2-2.4-3.3-5.4-3.3-9S9.8 5.4 12 3Z"/>',
    ];
    $navItems = [
        ['route' => 'admin.dashboard', 'label' => __('admin.nav.dashboard'), 'icon' => 'dashboard'],
        ['route' => 'admin.bookings', 'label' => __('admin.nav.bookings'), 'icon' => 'bookings', 'badge' => $pendingCount],
        ['route' => 'admin.vehicles', 'label' => __('admin.nav.vehicles'), 'icon' => 'vehicles'],
    ];
    $settingItems = [
        ['route' => 'admin.page-settings', 'label' => __('admin.nav.page_settings'), 'icon' => 'page'],
        ['route' => 'admin.pixel-settings', 'label' => __('admin.nav.pixels'), 'icon' => 'pixels'],
        ['route' => 'admin.change-password', 'label' => __('admin.nav.change_password'), 'icon' => 'password'],
    ];
    $current = collect(array_merge($navItems, $settingItems))->first(fn ($i) => request()->routeIs($i['route']));
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $current['label'] ?? 'Admin' }} — SEWOLAH Admin</title>
    <script>
      (function () {
        var t = null;
        try { t = localStorage.getItem('sewolah_admin_theme'); } catch (e) {}
        if (t !== 'light' && t !== 'dark') {
          t = window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
        }
        document.documentElement.setAttribute('data-theme', t);
      })();
    </script>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ $adminCssV }}">
    @livewireStyles
</head>
<body class="adm">
  <div class="adm-shell">
    <div class="adm-backdrop" data-sidebar-close hidden></div>

    <aside class="adm-sidebar" data-sidebar>
      <div class="adm-brand">
        <a href="{{ route('admin.dashboard') }}" aria-label="SEWOLAH Admin">
          <img src="{{ asset('images/home/logo-dark.png') }}" alt="SEWOLAH" class="logo-for-light">
          <img src="{{ asset('images/home/logo-light.png') }}" alt="SEWOLAH" class="logo-for-dark">
          <small>ADMIN PANEL</small>
        </a>
      </div>

      <nav class="adm-nav" aria-label="Admin">
        <p class="adm-nav__label">Utama</p>
        @foreach($navItems as $item)
          <a href="{{ route($item['route']) }}" @class(['is-active' => request()->routeIs($item['route'])])>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">{!! $icons[$item['icon']] !!}</svg>
            <span>{{ $item['label'] }}</span>
            @if(!empty($item['badge']))<span class="adm-nav__badge" title="Tempahan baru">{{ $item['badge'] }}</span>@endif
          </a>
        @endforeach
        <p class="adm-nav__label">Tetapan</p>
        @foreach($settingItems as $item)
          <a href="{{ route($item['route']) }}" @class(['is-active' => request()->routeIs($item['route'])])>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">{!! $icons[$item['icon']] !!}</svg>
            <span>{{ $item['label'] }}</span>
          </a>
        @endforeach
      </nav>

      <div class="adm-sidebar__foot">
        <a href="{{ route('home') }}" target="_blank" rel="noopener">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">{!! $icons['site'] !!}</svg>
          Lihat Website
        </a>
        <form method="POST" action="{{ route('admin.logout') }}">
          @csrf
          <button type="submit">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">{!! $icons['logout'] !!}</svg>
            {{ __('admin.nav.logout') }}
          </button>
        </form>
      </div>
    </aside>

    <div class="adm-main">
      <header class="adm-topbar">
        <button type="button" class="adm-iconbtn adm-menu-btn" data-sidebar-open aria-label="Menu">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
        </button>
        <p class="adm-topbar__title">Admin / <strong>{{ $current['label'] ?? 'Panel' }}</strong></p>
        <div class="adm-topbar__spacer"></div>
        <button type="button" class="adm-iconbtn adm-theme" data-theme-toggle aria-label="Tukar mod cerah / gelap" title="Tukar mod cerah / gelap">
          <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 14.5A8 8 0 0 1 9.5 4a8 8 0 1 0 10.5 10.5Z"/></svg>
          <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
        </button>
        @if($user)
          <div class="adm-user">
            <div class="adm-user__avatar">{{ mb_strtoupper(mb_substr($user->name ?? 'A', 0, 1)) }}</div>
            <div class="adm-user__meta">
              <p class="adm-user__name">{{ $user->name }}</p>
              <p class="adm-user__role">{{ $user->role === 'super_admin' ? 'Super Admin' : 'Admin' }}</p>
            </div>
          </div>
        @endif
      </header>

      <main class="adm-content">
        {{ $slot }}
      </main>
    </div>
  </div>

  <div class="adm-tip" data-tip-box hidden></div>

  @livewireScripts
  <script>
    (function () {
      var root = document.documentElement;
      document.addEventListener('click', function (e) {
        var t = e.target.closest('[data-theme-toggle]');
        if (t) {
          var next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
          root.setAttribute('data-theme', next);
          try { localStorage.setItem('sewolah_admin_theme', next); } catch (err) {}
        }
        var sidebar = document.querySelector('[data-sidebar]');
        var backdrop = document.querySelector('[data-sidebar-close]');
        if (e.target.closest('[data-sidebar-open]')) { sidebar.classList.add('is-open'); backdrop.hidden = false; }
        if (e.target.closest('[data-sidebar-close]')) { sidebar.classList.remove('is-open'); backdrop.hidden = true; }
      });

      // Chart tooltips: any element with data-tip
      var tip = document.querySelector('[data-tip-box]');
      document.addEventListener('mouseover', function (e) {
        var el = e.target.closest('[data-tip]');
        if (!el) { tip.hidden = true; return; }
        tip.textContent = '';
        var b = document.createElement('b');
        b.textContent = el.getAttribute('data-tip');
        tip.appendChild(b);
        var sub = el.getAttribute('data-tip-sub');
        if (sub) { tip.appendChild(document.createElement('br')); tip.appendChild(document.createTextNode(sub)); }
        tip.hidden = false;
        var r = el.getBoundingClientRect();
        tip.style.left = (r.left + r.width / 2) + 'px';
        tip.style.top = Math.max(r.top, 60) + 'px';
      });
      document.addEventListener('scroll', function () { tip.hidden = true; }, { passive: true });
    })();
  </script>
</body>
</html>
