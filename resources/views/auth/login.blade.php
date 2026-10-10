@php($adminCss = public_path('css/admin.css'))
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Log Masuk — SEWOLAH Admin</title>
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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ file_exists($adminCss) ? filemtime($adminCss) : '1' }}">
</head>
<body class="adm">
  <button type="button" class="adm-iconbtn adm-theme adm-login__theme" data-theme-toggle aria-label="Tukar mod cerah / gelap">
    <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 14.5A8 8 0 0 1 9.5 4a8 8 0 1 0 10.5 10.5Z"/></svg>
    <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
  </button>
  <main class="adm-login">
    <form method="POST" action="{{ route('admin.login.attempt') }}" class="adm-card adm-login__card adm-form" style="padding:32px">
      @csrf
      <div class="adm-login__brand">
        <img src="{{ asset('images/home/logo-dark.png') }}" alt="SEWOLAH" class="logo-for-light">
        <img src="{{ asset('images/home/logo-light.png') }}" alt="SEWOLAH" class="logo-for-dark">
        <p>ADMIN PANEL</p>
      </div>
      <label class="adm-field"><span class="adm-label">{{ __('admin.login.username') }}</span>
        <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="adm-input">
      </label>
      <label class="adm-field"><span class="adm-label">{{ __('admin.login.password') }}</span>
        <input type="password" name="password" required autocomplete="current-password" class="adm-input">
      </label>
      @error('email')<p class="adm-error">{{ $message }}</p>@enderror
      <button type="submit" class="adm-btn adm-btn--primary" style="min-height:46px">{{ __('admin.login.submit') }}</button>
    </form>
  </main>
  <script>
    document.addEventListener('click', function (e) {
      if (!e.target.closest('[data-theme-toggle]')) return;
      var root = document.documentElement;
      var next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
      root.setAttribute('data-theme', next);
      try { localStorage.setItem('sewolah_admin_theme', next); } catch (err) {}
    });
  </script>
</body>
</html>
