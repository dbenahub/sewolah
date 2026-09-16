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

    {{-- Meta / Google / TikTok Pixel (dynamic, from admin settings) --}}
    @if($pixelSettings->meta_pixel_id ?? false)
    <script>
      !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
      n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
      n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
      t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
      document,'script','https://connect.facebook.net/en_US/fbevents.js');
      fbq('init', '{{ $pixelSettings->meta_pixel_id }}');
      fbq('track', 'PageView');
      window.addEventListener('form-start', () => fbq('trackCustom', 'FormStart'));
      window.addEventListener('lead-submitted', () => fbq('track', 'Lead'));
    </script>
    @endif
</head>
<body class="font-sans bg-black text-white antialiased overflow-x-hidden" style="margin:0;">
    {{ $slot }}
    @livewireScripts
</body>
</html>
