<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('landing.meta_title') }}</title>
    <meta name="description" content="{{ __('landing.meta_description') }}">
    <meta property="og:title" content="{{ __('landing.meta_title') }}">
    <meta property="og:description" content="{{ __('landing.meta_description') }}">
    <meta property="og:image" content="{{ asset('images/hero-full.jpg') }}">
    <meta property="og:type" content="website">
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    {{-- Meta Pixel (dynamic, from admin settings or META_PIXEL_ID) --}}
    @if($pixelSettings->meta_pixel_id ?? false)
    <script>
      const sewolahMetaPixelId = @json((string) $pixelSettings->meta_pixel_id);
      const sewolahTrackedLeadEvents = new Set();

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

      window.addEventListener('form-start', () => {
        if (window.fbq) fbq('trackCustom', 'FormStart');
      });
      window.addEventListener('lead-submitted', (event) => {
        const eventId = event.detail?.eventId;
        if (!window.fbq || !eventId || sewolahTrackedLeadEvents.has(eventId)) return;
        sewolahTrackedLeadEvents.add(eventId);
        fbq('track', 'Lead', {}, { eventID: eventId });
      });

      if (localStorage.getItem('sewolah_marketing_consent') === 'granted') {
        loadSewolahMetaPixel();
      }
    </script>
    @endif
</head>
<body class="font-sans bg-black text-white antialiased overflow-x-hidden" style="margin:0;">
    {{ $slot }}
    @if($pixelSettings->meta_pixel_id ?? false)
      <div id="cookie-consent" class="fixed inset-x-4 bottom-4 z-[100] mx-auto hidden max-w-2xl rounded-2xl border border-white/15 bg-[#111] p-4 shadow-2xl md:p-5">
        <p class="m-0 text-sm font-bold">Kuki pemasaran</p>
        <p class="mt-1 text-xs leading-relaxed text-white/65">Kami menggunakan Meta Pixel untuk mengukur lawatan dan penghantaran borang. Baca <a href="{{ route('privacy-policy') }}" class="underline">Polisi Privasi</a>.</p>
        <div class="mt-3 flex gap-2">
          <button type="button" data-cookie-choice="declined" class="rounded-lg border border-white/20 px-4 py-2 text-xs font-bold">TOLAK</button>
          <button type="button" data-cookie-choice="granted" class="rounded-lg bg-brand-red px-4 py-2 text-xs font-bold text-white">BENARKAN</button>
        </div>
      </div>
      <script>
        const sewolahConsentBox = document.getElementById('cookie-consent');
        const sewolahConsent = localStorage.getItem('sewolah_marketing_consent');
        if (!sewolahConsent) sewolahConsentBox?.classList.remove('hidden');
        document.querySelectorAll('[data-cookie-choice]').forEach((button) => {
          button.addEventListener('click', () => {
            const choice = button.dataset.cookieChoice;
            localStorage.setItem('sewolah_marketing_consent', choice);
            sewolahConsentBox?.classList.add('hidden');
            if (choice === 'granted') loadSewolahMetaPixel();
          });
        });
      </script>
    @endif
    @livewireScripts
</body>
</html>
