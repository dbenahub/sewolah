<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('admin.panel_title') }} — SEWOLAH</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="font-sans bg-[#0A0A0A] text-white min-h-screen flex items-center justify-center p-6">
    <form method="POST" action="{{ route('admin.login.attempt') }}" class="w-full max-w-sm bg-[#141414] border border-white/8 rounded-2xl p-9 grid gap-4.5">
        @csrf
        <div class="grid gap-1 justify-items-center mb-1.5">
            <img src="{{ asset('images/logo-black.png') }}" alt="SEWOLAH" class="h-8">
            <p class="mt-2 text-[13px] font-bold tracking-wide text-white/50">{{ __('admin.panel_title') }}</p>
        </div>
        <label class="grid gap-1.5 text-[13px] font-semibold">{{ __('admin.login.username') }}
            <input type="email" name="email" required class="w-full box-border px-3.5 py-3 rounded-lg border border-white/15 bg-[#0A0A0A] text-white text-sm">
        </label>
        <label class="grid gap-1.5 text-[13px] font-semibold">{{ __('admin.login.password') }}
            <input type="password" name="password" required class="w-full box-border px-3.5 py-3 rounded-lg border border-white/15 bg-[#0A0A0A] text-white text-sm">
        </label>
        @error('email')
            <p class="m-0 text-[12.5px] text-red-400">{{ $message }}</p>
        @enderror
        <button type="submit" class="bg-brand-red text-white font-bold text-sm py-3.5 rounded-lg">{{ __('admin.login.submit') }}</button>
    </form>
</body>
</html>
