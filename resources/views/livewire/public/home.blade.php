@php
    $d = fn ($text) => str_replace(':days', (string) $minDays, $text);
    $waNumber = preg_replace('/[^0-9]/', '', (string) $pageSettings->whatsapp_number);
    $fleetImage = function ($vehicle) {
        $base = pathinfo((string) $vehicle->image_path, PATHINFO_FILENAME);
        $optimized = 'images/home/fleet/'.$base.'.jpg';
        return ($base && file_exists(public_path($optimized))) ? asset($optimized) : $vehicle->imageUrl();
    };
    $icons = [
        'individual' => '<path d="M8 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm8 1a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5ZM2.5 19c.6-3.2 2.8-5 5.5-5s4.9 1.8 5.5 5M14 14.3c.6-.2 1.3-.3 2-.3 2.4 0 4.3 1.6 4.8 4.4"/>',
        'corporate' => '<path d="M3.5 20.5h17M5.5 20.5V5.5l7-2v17M12.5 8.5h6v12M8.5 8h1M8.5 11.5h1M8.5 15h1M15 12h1M15 15.5h1"/>',
        'outstation' => '<path d="M2.5 16.5h19M6 13.5l-2.2-4.6 1.9-.6 3.4 3 4.3-1.4L9.9 4l2.2-.7 6.4 6.3 2.4-.8c.9-.3 1.8.2 2 1 .3.8-.2 1.6-1 1.9L7.4 15.4"/>',
        'event' => '<path d="m12 3 2.6 5.3 5.9.9-4.3 4.1 1 5.8L12 16.4l-5.2 2.7 1-5.8-4.3-4.1 5.9-.9L12 3Z"/>',
        'long_term' => '<path d="M4 6.5h16v13H4zM4 10.5h16M8.5 3.5v5M15.5 3.5v5M8 14h2M14 14h2M8 17h2"/>',
    ];
@endphp
<div>
  {{-- ============ HERO ============ --}}
  <section class="sw-hero" aria-labelledby="hero-title">
    <div class="sw-hero__media" aria-hidden="true">
      <img src="{{ asset('images/home/hero.jpg') }}" alt="" fetchpriority="high">
    </div>
    <div class="sw-container sw-hero__content">
      <p class="sw-eyebrow sw-eyebrow--light">{{ __('home.hero.eyebrow') }}</p>
      <h1 id="hero-title" class="sw-hero__title">{!! __('home.hero.title') !!}</h1>
      <p class="sw-hero__subtitle">{{ __('home.hero.subtitle') }}</p>
      <div class="sw-hero__actions">
        <a href="{{ route('booking.form') }}" class="sw-btn sw-btn--red sw-btn--lg">{{ __('home.hero.cta_primary') }} <span aria-hidden="true">→</span></a>
        <a href="#proses" class="sw-btn sw-btn--outline-light sw-btn--lg">{{ __('home.hero.cta_secondary') }}</a>
      </div>
      <ul class="sw-chips" role="list">
        @foreach(__('home.hero.chips') as $chip)
          <li>{{ $d($chip) }}</li>
        @endforeach
      </ul>
    </div>
  </section>

  {{-- ============ STATS ============ --}}
  <section class="sw-stats" aria-label="SEWOLAH">
    <div class="sw-container sw-stats__grid">
      @foreach(__('home.stats') as $stat)
        <div class="sw-stat" data-reveal>
          <p class="sw-stat__value">{{ $d($stat['value']) }}</p>
          <p class="sw-stat__label">{{ $d($stat['label']) }}</p>
        </div>
      @endforeach
    </div>
  </section>

  {{-- ============ ABOUT ============ --}}
  <section id="tentang" class="sw-section sw-section--paper">
    <div class="sw-container sw-about">
      <div class="sw-about__head" data-reveal>
        <p class="sw-eyebrow">{{ __('home.about.eyebrow') }}</p>
        <h2 class="sw-h2">{{ __('home.about.title') }}</h2>
      </div>
      <div class="sw-about__body" data-reveal>
        <p class="sw-lead">{{ __('home.about.lead') }}</p>
        <p>{{ __('home.about.body') }}</p>
        <div class="sw-about__partner">
          <span>{{ __('home.about.partner_label') }}</span>
          <strong>{{ __('home.about.partner_name') }}</strong>
        </div>
      </div>
    </div>
  </section>

  {{-- ============ PILLARS ============ --}}
  <section class="sw-section sw-section--paper sw-section--tight-top">
    <div class="sw-container">
      <div class="sw-section__head" data-reveal>
        <p class="sw-eyebrow">{{ __('home.pillars.eyebrow') }}</p>
        <h2 class="sw-h2">{{ __('home.pillars.title') }}</h2>
      </div>
      <div class="sw-pillars">
        @foreach(__('home.pillars.items') as $item)
          <article class="sw-pillar" data-reveal>
            <p class="sw-pillar__num">{{ $item['num'] }}</p>
            <h3 class="sw-pillar__title">{{ $item['title'] }}</h3>
            <p class="sw-pillar__desc">{{ $item['desc'] }}</p>
          </article>
        @endforeach
      </div>
    </div>
  </section>

  {{-- ============ SERVICES / CATEGORIES ============ --}}
  <section id="perkhidmatan" class="sw-section sw-section--ink">
    <div class="sw-container">
      <div class="sw-section__head sw-section__head--split" data-reveal>
        <div>
          <p class="sw-eyebrow">{{ __('home.services.eyebrow') }}</p>
          <h2 class="sw-h2">{{ __('home.services.title') }}</h2>
        </div>
        <p class="sw-section__sub">{{ __('home.services.subtitle') }}</p>
      </div>

      <div class="sw-services">
        <figure class="sw-services__feature" data-reveal>
          <img src="{{ asset('images/home/family.jpg') }}" alt="" loading="lazy">
          <figcaption>{{ __('home.about.signature') }}</figcaption>
        </figure>
        <div class="sw-services__list">
          @foreach(__('home.services.items') as $key => $item)
            <article class="sw-service" data-reveal>
              <div class="sw-service__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">{!! $icons[$key] !!}</svg>
              </div>
              <div class="sw-service__body">
                <h3 class="sw-service__title">{{ $item['title'] }}</h3>
                <p class="sw-service__desc">{{ $item['desc'] }}</p>
                <div class="sw-service__links">
                  <a href="{{ route('booking.form', ['kategori' => $key]) }}" class="sw-link">{{ __('home.services.cta') }} <span aria-hidden="true">→</span></a>
                  @if($key === 'outstation')
                    <a href="{{ route('landing') }}" class="sw-link sw-link--muted">{{ __('home.services.outstation_link') }}</a>
                  @endif
                </div>
              </div>
            </article>
          @endforeach
        </div>
      </div>
    </div>
  </section>

  {{-- ============ FLEET ============ --}}
  <section id="kenderaan" class="sw-section sw-section--paper">
    <div class="sw-container">
      <div class="sw-section__head sw-section__head--split" data-reveal>
        <div>
          <p class="sw-eyebrow">{{ __('home.fleet.eyebrow') }}</p>
          <h2 class="sw-h2">{{ __('home.fleet.title') }}</h2>
        </div>
        <p class="sw-section__sub">{{ __('home.fleet.subtitle') }}</p>
      </div>

      <ul class="sw-classes" role="list" data-reveal>
        @foreach(__('home.fleet.classes') as $class)
          <li>{{ $class }}</li>
        @endforeach
      </ul>

      <div class="sw-fleet">
        @foreach($vehicles as $vehicle)
          <article class="sw-car" data-reveal>
            <div class="sw-car__media">
              <img src="{{ $fleetImage($vehicle) }}" alt="{{ $vehicle->name }}" loading="lazy">
            </div>
            <div class="sw-car__body">
              <p class="sw-car__cat">{{ $vehicle->category }}</p>
              <h3 class="sw-car__name">{{ $vehicle->name }}</h3>
              <a href="{{ route('booking.form', ['kenderaan' => $vehicle->id]) }}" class="sw-link">{{ __('home.fleet.request') }} <span aria-hidden="true">→</span></a>
            </div>
          </article>
        @endforeach
      </div>
      <p class="sw-fleet__note" data-reveal>{{ __('home.fleet.note') }}</p>
    </div>
  </section>

  {{-- ============ COVERAGE ============ --}}
  <section id="liputan" class="sw-section sw-section--ink sw-coverage-section">
    <div class="sw-container">
      <div class="sw-section__head sw-section__head--split" data-reveal>
        <div>
          <p class="sw-eyebrow">{{ __('home.coverage.eyebrow') }}</p>
          <h2 class="sw-h2">{{ __('home.coverage.title') }}</h2>
        </div>
        <p class="sw-section__sub">{{ __('home.coverage.subtitle') }}</p>
      </div>
      <div class="sw-regions">
        @foreach(__('home.coverage.regions') as $i => $region)
          <article class="sw-region" data-reveal>
            <p class="sw-region__index">0{{ $i + 1 }}</p>
            <h3 class="sw-region__name">{{ $region['name'] }}</h3>
            <ul role="list">
              @foreach($region['states'] as $state)
                <li>{{ $state }}</li>
              @endforeach
            </ul>
          </article>
        @endforeach
      </div>
      <div class="sw-points" data-reveal>
        <p class="sw-points__title">{{ __('home.coverage.points_title') }}</p>
        <ul role="list">
          @foreach(__('home.coverage.points') as $point)
            <li>{{ $point }}</li>
          @endforeach
        </ul>
      </div>
    </div>
  </section>

  {{-- ============ PROCESS ============ --}}
  <section id="proses" class="sw-section sw-section--paper">
    <div class="sw-container">
      <div class="sw-section__head" data-reveal>
        <p class="sw-eyebrow">{{ __('home.process.eyebrow') }}</p>
        <h2 class="sw-h2">{{ __('home.process.title') }}</h2>
      </div>
      <ol class="sw-steps" role="list">
        @foreach(__('home.process.steps') as $step)
          <li class="sw-step" data-reveal>
            <p class="sw-step__num">{{ $step['num'] }}</p>
            <h3 class="sw-step__title">{{ $step['title'] }}</h3>
            <p class="sw-step__desc">{{ $step['desc'] }}</p>
          </li>
        @endforeach
      </ol>
    </div>
  </section>

  {{-- ============ POLICY ============ --}}
  <section id="polisi" class="sw-section sw-section--paper sw-section--tight-top">
    <div class="sw-container">
      <div class="sw-policy" data-reveal>
        <div class="sw-policy__main">
          <p class="sw-eyebrow sw-eyebrow--light">{{ __('home.policy.eyebrow') }}</p>
          <h2 class="sw-h2 sw-h2--light">{{ __('home.policy.title') }}</h2>
          <div class="sw-policy__grid">
            @foreach(__('home.policy.items') as $item)
              <div class="sw-policy__item">
                <h3>{{ $d($item['title']) }}</h3>
                <p>{{ $d($item['desc']) }}</p>
              </div>
            @endforeach
          </div>
        </div>
        <aside class="sw-policy__docs">
          <p class="sw-policy__docs-title">{{ __('home.policy.docs_title') }}</p>
          <ul role="list">
            @foreach(__('home.policy.docs') as $doc)
              <li>{{ $doc }}</li>
            @endforeach
          </ul>
          <p class="sw-policy__docs-note">{{ __('home.policy.docs_note') }}</p>
        </aside>
      </div>
    </div>
  </section>

  {{-- ============ FAQ ============ --}}
  <section id="soalan" class="sw-section sw-section--paper sw-section--tight-top">
    <div class="sw-container sw-faq">
      <div class="sw-faq__head" data-reveal>
        <p class="sw-eyebrow">{{ __('home.faq.eyebrow') }}</p>
        <h2 class="sw-h2">{{ __('home.faq.title') }}</h2>
      </div>
      <div class="sw-faq__list">
        @foreach(__('home.faq.items') as $i => $item)
          <details class="sw-faq__item" @if($i === 0) open @endif>
            <summary>{{ $d($item['q']) }}</summary>
            <p>{{ $d($item['a']) }}</p>
          </details>
        @endforeach
      </div>
    </div>
  </section>

  {{-- ============ FINAL CTA ============ --}}
  <section class="sw-final">
    <div class="sw-container sw-final__inner" data-reveal>
      <p class="sw-eyebrow sw-eyebrow--light">{{ __('home.final.eyebrow') }}</p>
      <h2 class="sw-final__title">{{ __('home.final.title') }}</h2>
      <p class="sw-final__copy">{{ $d(__('home.final.copy')) }}</p>
      <div class="sw-final__actions">
        <a href="{{ route('booking.form') }}" class="sw-btn sw-btn--white sw-btn--lg">{{ __('home.final.cta') }} <span aria-hidden="true">→</span></a>
        <a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener" class="sw-link sw-link--light">{{ __('home.final.secondary') }}</a>
      </div>
    </div>
  </section>
</div>
