@php
    $title = $title ?? __('home.meta_title');
    $description = $description ?? __('home.meta_description');
    $bodyClass = $bodyClass ?? '';
    $hideMobileCta = $hideMobileCta ?? false;
    $pageSettings = $pageSettings ?? \App\Models\PageSetting::current();
    $waNumber = preg_replace('/[^0-9]/', '', (string) $pageSettings->whatsapp_number);
    $cssPath = public_path('css/sewolah.css');
    $cssVersion = file_exists($cssPath) ? filemtime($cssPath) : '1';
    $isHome = request()->routeIs('home');
    $navBase = $isHome ? '' : route('home');
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <script>document.documentElement.classList.add('js');</script>
    <title>{{ $title }}</title>
    <meta name="description" content="{{ $description }}">
    <meta name="theme-color" content="#0B0B0C">
    <meta property="og:site_name" content="SEWOLAH">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:image" content="{{ asset('images/home/hero.jpg') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/sewolah.css') }}?v={{ $cssVersion }}">
    @livewireStyles

    @if($pixelSettings->meta_pixel_id ?? false)
    <script>
      const sewolahMetaPixelId = @json((string) $pixelSettings->meta_pixel_id);
      const sewolahTrackedLeadEvents = new Set();
      function sewolahConsentValue() { try { return localStorage.getItem('sewolah_marketing_consent'); } catch (e) { return null; } }
      function loadSewolahMetaPixel() {
        if (window.fbq) return;
        !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
        n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
        document,'script','https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', sewolahMetaPixelId);
        fbq('track', 'PageView');
      }
      window.addEventListener('form-start', () => { if (window.fbq) fbq('trackCustom', 'FormStart'); });
      window.addEventListener('lead-submitted', (event) => {
        const eventId = event.detail?.eventId;
        if (!window.fbq || !eventId || sewolahTrackedLeadEvents.has(eventId)) return;
        sewolahTrackedLeadEvents.add(eventId);
        fbq('track', 'Lead', {}, { eventID: eventId });
      });
      if (sewolahConsentValue() === 'granted') loadSewolahMetaPixel();
    </script>
    @endif
</head>
<body class="sw {{ $bodyClass }}">
    <a href="#main" class="sw-skip">Skip to content</a>

    {{-- HEADER --}}
    <header class="sw-header" data-header>
      <div class="sw-container sw-header__inner">
        <a href="{{ route('home') }}" class="sw-header__logo" aria-label="SEWOLAH">
          <img src="{{ asset('images/home/logo-light.png') }}" alt="SEWOLAH — Rent With Confidence" width="640" height="122">
        </a>

        <nav class="sw-nav" aria-label="Primary">
          <a href="{{ $navBase }}#tentang">{{ __('home.nav.about') }}</a>
          <a href="{{ $navBase }}#perkhidmatan">{{ __('home.nav.services') }}</a>
          <a href="{{ $navBase }}#kenderaan">{{ __('home.nav.fleet') }}</a>
          <a href="{{ $navBase }}#liputan">{{ __('home.nav.coverage') }}</a>
          <a href="{{ $navBase }}#proses">{{ __('home.nav.process') }}</a>
          <a href="{{ route('landing') }}">{{ __('home.nav.outstation') }}</a>
        </nav>

        <div class="sw-header__actions">
          <div class="sw-lang" aria-label="Language">
            <a href="{{ route('lang.switch', 'ms') }}" @class(['is-active' => app()->getLocale() === 'ms'])>BM</a>
            <span aria-hidden="true">/</span>
            <a href="{{ route('lang.switch', 'en') }}" @class(['is-active' => app()->getLocale() === 'en'])>EN</a>
          </div>
          @unless(request()->routeIs('booking.form'))
            <a href="{{ route('booking.form') }}" class="sw-btn sw-btn--red sw-btn--sm sw-header__cta">{{ __('home.nav.cta') }}</a>
          @endunless
          <button type="button" class="sw-burger" data-menu-open aria-label="{{ __('home.nav.menu') }}" aria-expanded="false">
            <span></span><span></span>
          </button>
        </div>
      </div>
    </header>

    {{-- MOBILE MENU --}}
    <div class="sw-menu" data-menu hidden>
      <div class="sw-menu__top">
        <img src="{{ asset('images/home/logo-light.png') }}" alt="SEWOLAH" class="sw-menu__logo">
        <button type="button" class="sw-menu__close" data-menu-close>{{ __('home.nav.close') }} ✕</button>
      </div>
      <nav class="sw-menu__nav">
        <a href="{{ $navBase }}#tentang" data-menu-link>{{ __('home.nav.about') }}</a>
        <a href="{{ $navBase }}#perkhidmatan" data-menu-link>{{ __('home.nav.services') }}</a>
        <a href="{{ $navBase }}#kenderaan" data-menu-link>{{ __('home.nav.fleet') }}</a>
        <a href="{{ $navBase }}#liputan" data-menu-link>{{ __('home.nav.coverage') }}</a>
        <a href="{{ $navBase }}#proses" data-menu-link>{{ __('home.nav.process') }}</a>
        <a href="{{ $navBase }}#soalan" data-menu-link>{{ __('home.nav.faq') }}</a>
        <a href="{{ route('landing') }}">{{ __('home.nav.outstation') }}</a>
      </nav>
      <a href="{{ route('booking.form') }}" class="sw-btn sw-btn--red sw-btn--block">{{ __('home.nav.cta') }}</a>
    </div>

    <main id="main">
      {{ $slot }}
    </main>

    {{-- FOOTER --}}
    <footer class="sw-footer">
      <div class="sw-container">
        <div class="sw-footer__grid">
          <div class="sw-footer__brand">
            <img src="{{ asset('images/home/logo-light.png') }}" alt="SEWOLAH" class="sw-footer__logo" loading="lazy">
            <p>{{ __('home.footer.tagline') }}</p>
          </div>
          <div>
            <p class="sw-footer__title">{{ __('home.footer.col_services') }}</p>
            <ul>
              <li><a href="{{ route('booking.form') }}">{{ __('home.footer.form') }}</a></li>
              <li><a href="{{ route('landing') }}">{{ __('home.footer.outstation') }}</a></li>
              <li><a href="{{ route('booking.form', ['kategori' => 'corporate']) }}">{{ __('home.services.items.corporate.title') }}</a></li>
              <li><a href="{{ route('booking.form', ['kategori' => 'long_term']) }}">{{ __('home.services.items.long_term.title') }}</a></li>
            </ul>
          </div>
          <div>
            <p class="sw-footer__title">{{ __('home.footer.col_company') }}</p>
            <ul>
              <li><a href="{{ $navBase }}#tentang">{{ __('home.nav.about') }}</a></li>
              <li><a href="{{ $navBase }}#proses">{{ __('home.nav.process') }}</a></li>
              <li><a href="{{ route('privacy-policy') }}">{{ __('home.footer.privacy') }}</a></li>
              <li><a href="{{ route('terms') }}">{{ __('home.footer.terms') }}</a></li>
            </ul>
          </div>
          <div>
            <p class="sw-footer__title">{{ __('home.footer.col_contact') }}</p>
            <ul>
              <li><a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener">{{ __('home.footer.whatsapp') }}</a></li>
              <li><span>www.sewolah.com</span></li>
            </ul>
          </div>
        </div>
        <div class="sw-footer__bottom">
          <p>{{ __('home.footer.rights', ['year' => now()->year]) }}</p>
          <p>{{ __('home.footer.note') }}</p>
        </div>
      </div>
    </footer>

    @unless($hideMobileCta)
      <div class="sw-mobile-cta">
        <a href="{{ route('booking.form') }}" class="sw-btn sw-btn--red sw-btn--block">{{ __('home.mobile_cta') }}</a>
      </div>
    @endunless

    @if($pixelSettings->meta_pixel_id ?? false)
      <div id="cookie-consent" class="sw-consent" hidden>
        <p class="sw-consent__title">{{ app()->getLocale() === 'en' ? 'Marketing cookies' : 'Kuki pemasaran' }}</p>
        <p class="sw-consent__copy">{{ app()->getLocale() === 'en' ? 'We use Meta Pixel to measure visits and form submissions.' : 'Kami menggunakan Meta Pixel untuk mengukur lawatan dan penghantaran borang.' }} <a href="{{ route('privacy-policy') }}">{{ __('home.footer.privacy') }}</a>.</p>
        <div class="sw-consent__actions">
          <button type="button" data-cookie-choice="declined" class="sw-btn sw-btn--ghost sw-btn--sm">{{ app()->getLocale() === 'en' ? 'Decline' : 'Tolak' }}</button>
          <button type="button" data-cookie-choice="granted" class="sw-btn sw-btn--red sw-btn--sm">{{ app()->getLocale() === 'en' ? 'Allow' : 'Benarkan' }}</button>
        </div>
      </div>
      <script>
        (function () {
          const box = document.getElementById('cookie-consent');
          if (!sewolahConsentValue()) box.hidden = false;
          document.querySelectorAll('[data-cookie-choice]').forEach((button) => {
            button.addEventListener('click', () => {
              const choice = button.dataset.cookieChoice;
              try { localStorage.setItem('sewolah_marketing_consent', choice); } catch (e) {}
              box.hidden = true;
              if (choice === 'granted') loadSewolahMetaPixel();
            });
          });
        })();
      </script>
    @endif

    @livewireScripts
    <script>
      (function () {
        const header = document.querySelector('[data-header]');
        const onScroll = () => {
          header && header.classList.toggle('is-scrolled', window.scrollY > 24);
          document.body.classList.toggle('has-scrolled', window.scrollY > 480);
        };
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });

        const menu = document.querySelector('[data-menu]');
        const openBtn = document.querySelector('[data-menu-open]');
        const close = () => { menu.hidden = true; document.body.classList.remove('menu-open'); openBtn.setAttribute('aria-expanded', 'false'); };
        openBtn?.addEventListener('click', () => { menu.hidden = false; document.body.classList.add('menu-open'); openBtn.setAttribute('aria-expanded', 'true'); });
        document.querySelector('[data-menu-close]')?.addEventListener('click', close);
        document.querySelectorAll('[data-menu-link]').forEach((a) => a.addEventListener('click', close));

        window.addEventListener('form-step-changed', () => {
          const card = document.getElementById('borang');
          if (!card) return;
          const top = card.getBoundingClientRect().top + window.scrollY - 96;
          if (window.scrollY > top) window.scrollTo({ top, behavior: 'smooth' });
        });

        const reveal = document.querySelectorAll('[data-reveal]');
        if ('IntersectionObserver' in window) {
          const io = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
              if (entry.isIntersecting) { entry.target.classList.add('is-visible'); io.unobserve(entry.target); }
            });
          }, { rootMargin: '0px 0px -8% 0px' });
          reveal.forEach((el) => io.observe(el));
        } else {
          reveal.forEach((el) => el.classList.add('is-visible'));
        }
      })();
    </script>
</body>
</html>
