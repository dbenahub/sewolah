<div>
  {{-- HEADER --}}
  <header class="sticky top-0 z-50 bg-black/85 backdrop-blur border-b border-white/10">
    <div class="max-w-7xl mx-auto px-6 py-3.5 flex items-center justify-between gap-5">
      <a href="{{ route('landing') }}"><img src="{{ asset('images/logo-black.png') }}" alt="SEWOLAH" class="h-10"></a>
      <nav class="hidden md:flex gap-6 text-[13px] font-semibold tracking-wide flex-wrap">
        <a href="#kereta" class="text-white hover:text-brand-red">{{ __('landing.nav.vehicles') }}</a>
        <a href="#cara" class="text-white hover:text-brand-red">{{ __('landing.nav.how') }}</a>
        <a href="#booking-form" class="text-white hover:text-brand-red">{{ __('landing.nav.booking') }}</a>
      </nav>
      <div class="flex items-center gap-3">
        @livewire('public.language-switcher')
        <a href="#booking-form" class="bg-brand-red text-white font-bold text-[13px] tracking-wide px-5 py-2.5 rounded-lg whitespace-nowrap">{{ __('landing.nav.cta') }}</a>
      </div>
    </div>
  </header>

  {{-- HERO --}}
  <section class="max-w-7xl mx-auto px-6 py-10 md:py-20 grid md:grid-cols-2 gap-12 items-center">
    <div class="grid gap-5 max-w-xl">
      <span class="inline-block w-fit text-xs font-bold tracking-wider text-brand-red border border-brand-red/40 rounded-full px-3.5 py-1.5">{{ __('landing.hero.badge') }}</span>
      <h1 class="m-0 text-4xl md:text-6xl font-extrabold leading-tight tracking-tight">{!! __('landing.hero.title') !!}</h1>
      <p class="m-0 text-base md:text-lg leading-relaxed text-white/70 max-w-lg">{{ __('landing.hero.subtitle') }}</p>
      <p class="m-0 text-sm font-bold tracking-wide">{!! __('landing.hero.trust_line') !!}</p>
      <div class="flex flex-wrap gap-3.5 mt-1.5">
        <a href="#booking-form" class="bg-brand-red text-white font-bold text-[15px] px-7 py-4 rounded-xl">{{ __('landing.hero.cta_primary') }}</a>
        <a href="#kereta" class="bg-transparent text-white font-bold text-[15px] px-7 py-4 rounded-xl border border-white/30">{{ __('landing.hero.cta_secondary') }}</a>
      </div>
      <p class="m-0 mt-1 text-[13px] text-white/50 max-w-md">{{ __('landing.hero.microcopy') }}</p>
    </div>
    <div class="rounded-[20px] overflow-hidden border border-white/10">
      <img src="{{ asset('images/hero-full.jpg') }}" alt="SEWOLAH premium fleet" class="w-full block" loading="eager">
    </div>
  </section>

  {{-- PARTNER TRUST BAR --}}
  <section class="bg-[#161616] border-y border-white/6">
    <div class="max-w-7xl mx-auto px-6 py-8 flex flex-wrap gap-6 items-center justify-between">
      <div>
        <p class="m-0 mb-1.5 text-xs font-bold tracking-widest text-brand-red">{{ __('landing.partner.label') }}</p>
        <p class="m-0 text-xl font-extrabold">{{ __('landing.partner.name') }}</p>
      </div>
      <p class="m-0 max-w-xl text-sm leading-relaxed text-white/65">{{ __('landing.partner.copy') }}</p>
    </div>
  </section>

  {{-- PROBLEM --}}
  <section class="max-w-7xl mx-auto px-6 py-14 md:py-24">
    <h2 class="m-0 mb-10 text-3xl md:text-4xl font-extrabold max-w-xl">{{ __('landing.problem.title') }}</h2>
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
      @foreach(__('landing.problem.items') as $item)
        <div class="bg-[#0d0d0d] border border-white/8 rounded-2xl p-6 grid gap-2.5 content-start">
          <div class="w-9 h-[3px] bg-brand-red rounded"></div>
          <p class="m-0 text-[17px] font-bold">{{ $item['title'] }}</p>
          <p class="m-0 text-sm leading-relaxed text-white/60">{{ $item['desc'] }}</p>
        </div>
      @endforeach
    </div>
    <p class="mt-10 text-xl font-bold text-center">{{ __('landing.problem.closing') }}</p>
  </section>

  {{-- SOLUTION --}}
  <section class="max-w-7xl mx-auto px-6 pb-14 md:pb-24">
    <div class="bg-[#0d0d0d] border border-white/8 rounded-3xl p-8 md:p-14 grid gap-8">
      <div class="max-w-2xl">
        <h2 class="m-0 mb-3.5 text-2xl md:text-4xl font-extrabold">{{ __('landing.solution.title') }}</h2>
        <p class="m-0 text-[15px] leading-relaxed text-white/65">{{ __('landing.solution.copy') }}</p>
      </div>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach(__('landing.solution.journey') as $j)
          <div class="grid gap-2.5 p-5 rounded-2xl bg-black border border-white/6 text-center">
            <p class="m-0 text-xs font-bold tracking-wide text-brand-red">{{ $j['step'] }}</p>
            <p class="m-0 text-base font-bold">{{ $j['label'] }}</p>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  {{-- FEATURED VEHICLES --}}
  <section id="kereta" class="bg-brand-light text-[#0A0A0A] py-14 md:py-24 px-6">
    <div class="max-w-7xl mx-auto">
      <h2 class="m-0 mb-2.5 text-3xl md:text-4xl font-extrabold">{{ __('landing.vehicles_section.title') }}</h2>
      <p class="m-0 mb-10 text-sm text-black/55">{{ __('landing.vehicles_section.subtitle') }}</p>
      <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($vehicles as $v)
          <div class="bg-white rounded-2xl overflow-hidden border border-black/8 grid">
            <div class="h-44 overflow-hidden">
              <img src="{{ $v->imageUrl() }}" alt="{{ $v->name }}" class="w-full h-full object-cover block" loading="lazy">
            </div>
            <div class="p-6 grid gap-3">
              <div>
                <p class="m-0 mb-0.5 text-xs font-bold tracking-wide text-brand-red">{{ $v->category }}</p>
                <p class="m-0 text-xl font-extrabold">{{ $v->name }}</p>
              </div>
              <ul class="m-0 pl-4 text-[13.5px] leading-relaxed text-black/65">
                @foreach($v->tagsFor(app()->getLocale()) as $tag)
                  <li>{{ $tag }}</li>
                @endforeach
              </ul>
              <button type="button" wire:click="$dispatchTo('public.booking-form', 'selectVehicle', { vehicleId: {{ $v->id }} })" onclick="document.getElementById('booking-form').scrollIntoView({behavior:'smooth'})"
                class="justify-self-start mt-1.5 bg-black text-white font-bold text-[13.5px] px-5 py-3 rounded-lg">{{ $v->cta_label }}</button>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  {{-- OTHER VEHICLE OPTIONS --}}
  <section class="bg-brand-light text-[#0A0A0A] pb-14 md:pb-24 px-6">
    <div class="max-w-7xl mx-auto bg-white border border-black/8 rounded-[20px] p-7 md:p-12 grid gap-6">
      <div class="max-w-xl">
        <h2 class="m-0 mb-2.5 text-2xl md:text-3xl font-extrabold">{{ __('landing.other_vehicles.title') }}</h2>
        <p class="m-0 mb-1 text-[15px] font-semibold">{{ __('landing.other_vehicles.subtitle') }}</p>
        <p class="m-0 text-sm leading-relaxed text-black/60">{{ __('landing.other_vehicles.copy') }}</p>
      </div>
      <div class="flex flex-wrap gap-2.5">
        @foreach(__('landing.other_vehicles.categories') as $cat)
          <span class="bg-brand-light border border-black/10 rounded-full px-4 py-2 text-[13px] font-semibold">{{ $cat }}</span>
        @endforeach
      </div>
      <p class="m-0 text-sm leading-relaxed text-black/60 max-w-2xl">{{ __('landing.other_vehicles.copy2') }}</p>
      <a href="#booking-form" class="justify-self-start bg-brand-red text-white font-bold text-sm px-6 py-3.5 rounded-lg">{{ __('landing.other_vehicles.cta') }}</a>
    </div>
  </section>

  {{-- FAMILY TRAVEL --}}
  <section class="max-w-7xl mx-auto px-6 py-12 md:py-20 grid md:grid-cols-2 gap-8 items-center">
    <div class="rounded-[20px] overflow-hidden h-64 md:h-72">
      <img src="{{ asset('images/family-travel.jpg') }}" alt="Family traveller at KLIA arrival" class="w-full h-full object-cover block" loading="lazy">
    </div>
    <div class="grid gap-3.5">
      <h2 class="m-0 text-2xl md:text-4xl font-extrabold">{{ __('landing.family.title') }}</h2>
      <p class="m-0 text-[15px] leading-relaxed text-white/65">{{ __('landing.family.copy') }}</p>
      <p class="m-0 text-[13px] font-bold tracking-wide text-brand-red">{{ __('landing.family.recommended') }}</p>
      <a href="#kereta" class="justify-self-start mt-1.5 bg-white text-black font-bold text-sm px-6 py-3.5 rounded-lg">{{ __('landing.family.cta') }}</a>
    </div>
  </section>

  {{-- BUSINESS TRAVEL --}}
  <section class="max-w-7xl mx-auto px-6 pb-12 md:pb-20 grid md:grid-cols-2 gap-8 items-center">
    <div class="grid gap-3.5 md:order-2">
      <h2 class="m-0 text-2xl md:text-4xl font-extrabold">{{ __('landing.business.title') }}</h2>
      <p class="m-0 text-[15px] leading-relaxed text-white/65">{{ __('landing.business.copy') }}</p>
      <p class="m-0 text-[13px] font-bold tracking-wide text-brand-red">{{ __('landing.business.recommended') }}</p>
      <a href="#kereta" class="justify-self-start mt-1.5 bg-white text-black font-bold text-sm px-6 py-3.5 rounded-lg">{{ __('landing.business.cta') }}</a>
    </div>
    <div class="rounded-[20px] overflow-hidden h-64 md:h-72 md:order-1">
      <img src="{{ asset('images/business-travel.jpg') }}" alt="Executive traveller, corporate arrival" class="w-full h-full object-cover block" loading="lazy">
    </div>
  </section>

  {{-- WHY SEWOLAH --}}
  <section class="max-w-7xl mx-auto px-6 py-14 md:py-24">
    <h2 class="m-0 mb-10 text-3xl md:text-4xl font-extrabold">{{ __('landing.why.title') }}</h2>
    <div class="grid sm:grid-cols-2 lg:grid-cols-5 gap-5.5">
      @foreach(__('landing.why.items') as $w)
        <div class="grid gap-2">
          <p class="m-0 text-[15px] font-extrabold text-brand-red">{{ $w['title'] }}</p>
          <p class="m-0 text-sm leading-relaxed text-white/60">{{ $w['desc'] }}</p>
        </div>
      @endforeach
    </div>
  </section>

  {{-- HOW IT WORKS --}}
  <section id="cara" class="bg-brand-light text-[#0A0A0A] py-14 md:py-24 px-6">
    <div class="max-w-7xl mx-auto">
      <h2 class="m-0 mb-10 text-3xl md:text-4xl font-extrabold max-w-xl">{{ __('landing.how.title') }}</h2>
      <div class="grid sm:grid-cols-2 lg:grid-cols-5 gap-5">
        @foreach(__('landing.how.steps') as $s)
          <div class="bg-white border border-black/8 rounded-2xl p-5.5 grid gap-2">
            <p class="m-0 text-2xl font-extrabold text-brand-red">{{ $s['num'] }}</p>
            <p class="m-0 text-[15px] font-bold">{{ $s['title'] }}</p>
            <p class="m-0 text-[13px] leading-relaxed text-black/60">{{ $s['desc'] }}</p>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  {{-- BOOKING FORM --}}
  <section id="booking-form" class="bg-brand-light text-[#0A0A0A] pb-14 md:pb-28 px-6">
    <div class="max-w-3xl mx-auto">
      @livewire('public.booking-form')
    </div>
  </section>

  {{-- FINAL CTA --}}
  <section class="max-w-7xl mx-auto px-6 pb-14 md:pb-24">
    <div class="bg-gradient-to-b from-[#141414] to-black border border-white/8 rounded-3xl p-10 md:p-16 text-center grid gap-4 justify-items-center">
      <h2 class="m-0 text-3xl md:text-5xl font-extrabold max-w-xl">{{ __('landing.final_cta.title') }}</h2>
      <p class="m-0 text-lg font-bold text-brand-red">{{ __('landing.final_cta.highlight') }}</p>
      <p class="m-0 max-w-xl text-[15px] leading-relaxed text-white/65">{{ __('landing.final_cta.copy') }}</p>
      <a href="#booking-form" class="mt-2 bg-brand-red text-white font-bold text-[15px] px-7 py-4 rounded-xl">{{ __('landing.final_cta.cta') }}</a>
    </div>
  </section>

  {{-- FOOTER --}}
  <footer class="border-t border-white/8">
    <div class="max-w-7xl mx-auto px-6 py-12 grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
      <div class="grid gap-3 content-start">
        <img src="{{ asset('images/logo-black.png') }}" alt="SEWOLAH" class="h-9">
        <p class="m-0 text-[13px] text-white/55">{{ __('landing.footer.tagline') }}</p>
        <p class="m-0 text-xs text-white/40">{{ __('landing.footer.segments') }}</p>
        <p class="m-0 text-xs text-white/40">{{ __('landing.footer.location') }}</p>
      </div>
      <div class="grid gap-2.5 content-start">
        <p class="m-0 mb-1 text-[13px] font-bold tracking-wide">{{ __('landing.footer.nav_title') }}</p>
        <a href="#kereta" class="text-[13px] text-white/60">{{ __('landing.nav.vehicles') }}</a>
        <a href="#cara" class="text-[13px] text-white/60">{{ __('landing.nav.how') }}</a>
        <a href="#booking-form" class="text-[13px] text-white/60">{{ __('landing.nav.booking') }}</a>
        <a href="{{ url('/privacy-policy') }}" class="text-[13px] text-white/60">{{ __('landing.footer.privacy') }}</a>
        <a href="{{ url('/terms') }}" class="text-[13px] text-white/60">{{ __('landing.footer.terms') }}</a>
      </div>
      <div class="grid gap-2.5 content-start">
        <p class="m-0 mb-1 text-[13px] font-bold tracking-wide">{{ __('landing.footer.partner_title') }}</p>
        <p class="m-0 text-[13px] text-white/60">{{ __('landing.partner.name') }}</p>
        <a href="https://www.sewolah.com" class="text-[13px] text-white/60">www.sewolah.com</a>
      </div>
    </div>
    <p class="m-0 text-center py-4 text-xs text-white/35 border-t border-white/6">{{ __('landing.footer.rights') }}</p>
  </footer>

  {{-- FLOATING WHATSAPP BUTTON --}}
  <a href="https://wa.me/{{ $pageSettings->whatsapp_number }}" target="_blank" rel="noopener" aria-label="Chat WhatsApp"
     class="fixed right-5 bottom-5 md:bottom-5 z-[61] w-14 h-14 rounded-full bg-[#25D366] flex items-center justify-center shadow-lg">
    <svg width="28" height="28" viewBox="0 0 24 24" fill="none">
      <path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5.1-1.3A10 10 0 1 0 12 2Z" fill="#fff"/>
      <path d="M12 3.6a8.4 8.4 0 0 0-7.2 12.7l.2.4-.9 3.3 3.4-.9.4.2A8.4 8.4 0 1 0 12 3.6Z" fill="#25D366"/>
      <path d="M9.1 7.8c-.2-.5-.4-.5-.7-.5h-.5c-.2 0-.5.1-.7.4-.2.2-.8.8-.8 1.9s.8 2.2.9 2.3c.1.2 1.5 2.5 3.8 3.4 1.9.8 2.3.6 2.7.6.4 0 1.3-.5 1.5-1 .2-.5.2-1 .1-1.1-.1-.1-.2-.2-.4-.3l-1.5-.7c-.2-.1-.4-.1-.5.1l-.6.8c-.1.2-.2.2-.4.1-.4-.2-1.1-.6-1.7-1.2-.6-.6-1-1.3-1.1-1.5-.1-.2 0-.3.1-.4l.5-.6c.1-.2.1-.4 0-.5L9.1 7.8Z" fill="#fff"/>
    </svg>
  </a>

  {{-- STICKY MOBILE CTA --}}
  <div class="md:hidden fixed left-0 right-0 bottom-0 z-[60] p-3 bg-black/90 backdrop-blur border-t border-white/10">
    <a href="#booking-form" class="block text-center bg-brand-red text-white font-bold text-[15px] py-3.5 rounded-xl min-h-[24px] animate-sw-pulse">{{ __('landing.mobile_cta') }}</a>
  </div>

  <script>
    window.addEventListener('scroll-to-booking-form', () => {
      document.getElementById('booking-form')?.scrollIntoView({ behavior: 'smooth' });
    });
    window.addEventListener('open-whatsapp', (e) => {
      window.open(e.detail.url, '_blank');
    });
  </script>
</div>
