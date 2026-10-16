<div class="sw-formpage">
  <section class="sw-formhero">
    <div class="sw-container">
      <p class="sw-eyebrow sw-eyebrow--light">SEWOLAH</p>
      <h1 class="sw-formhero__title">Terma &amp; Syarat Sewaan</h1>
      <p class="sw-formhero__intro">Polisi kelayakan, bayaran, deposit, pembatalan, minyak dan penggunaan kenderaan. Sila baca sebelum membuat tempahan.</p>
    </div>
  </section>
  <div class="sw-container sw-formwrap sw-formwrap--single">
    <article class="sw-formcard">
      @include('partials.rental-terms')
      <div class="sw-factions" style="margin-top:28px;flex-wrap:wrap">
        <a href="{{ route('booking.form') }}" class="sw-btn sw-btn--red">Mohon Tempahan <span aria-hidden="true">→</span></a>
      </div>
      <p class="sw-disclaimer">Dikemas kini {{ now()->locale('ms')->translatedFormat('F Y') }}. SEWOLAH berhak meminda terma ini; terma yang berkuat kuasa pada tarikh tempahan disahkan akan terpakai.</p>
    </article>
  </div>
</div>
