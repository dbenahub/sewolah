<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('admin.panel_title') }} — SEWOLAH</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans bg-[#0A0A0A] text-white min-h-screen antialiased">
    <div class="grid md:grid-cols-[220px_minmax(0,1fr)] min-h-screen">
        <aside class="bg-[#141414] border-r border-white/8 p-4 grid gap-1.5 content-start md:h-screen md:sticky md:top-0">
            <div class="-m-1 mb-4 p-3.5 bg-[#0A0A0A] rounded-lg">
                <img src="{{ asset('images/logo-black.png') }}" alt="SEWOLAH" class="h-7">
            </div>
            @php($navItems = [
                ['route' => 'admin.dashboard', 'label' => __('admin.nav.dashboard')],
                ['route' => 'admin.bookings', 'label' => __('admin.nav.bookings')],
                ['route' => 'admin.vehicles', 'label' => __('admin.nav.vehicles')],
                ['route' => 'admin.pixel-settings', 'label' => __('admin.nav.pixels')],
                ['route' => 'admin.page-settings', 'label' => __('admin.nav.page_settings')],
                ['route' => 'admin.change-password', 'label' => __('admin.nav.change_password')],
            ])
            @foreach($navItems as $item)
                <a href="{{ route($item['route']) }}"
                   class="w-full text-left {{ request()->routeIs($item['route']) ? 'bg-brand-red' : 'bg-transparent' }} text-white text-[13.5px] font-bold px-3 py-2.5 rounded-lg">
                   {{ $item['label'] }}
                </a>
            @endforeach
            <form method="POST" action="{{ route('admin.logout') }}" class="mt-6 pt-4 border-t border-white/8">
                @csrf
                <button type="submit" class="w-full text-left bg-transparent text-white/55 text-[13.5px] font-semibold px-3 py-2.5 rounded-lg">{{ __('admin.nav.logout') }}</button>
            </form>
        </aside>
        <main class="p-5 md:p-9 grid gap-6 content-start">
            {{ $slot }}
        </main>
    </div>
    @livewireScripts
</body>
</html>
