@php
  $rm = fn ($v) => \App\Support\Pricing::rm($v);
  $steps = ['Tawaran', 'Kelayakan', 'Bayaran', 'Disahkan', 'Selesai'];
  $current = match ($view) {
      'verify', 'reviewing', 'rejected' => 1,
      'pay', 'payment_review' => 2,
      'confirmed' => 3,
      'completed' => 4,
      default => 0,
  };
  $pillOk = in_array($view, ['confirmed', 'completed'], true);
  $statusText = match ($view) {
      'verify' => 'Lengkapkan semakan kelayakan',
      'reviewing' => 'Dokumen sedang disemak',
      'pay' => 'Kelayakan lulus — sila buat bayaran',
      'payment_review' => 'Bayaran sedang disahkan',
      'confirmed' => 'Tempahan disahkan',
      'completed' => 'Sewaan selesai',
      'rejected' => 'Tidak dapat diteruskan',
      'cancelled' => 'Tempahan dibatalkan',
  };
  $docIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 16V4m0 0-4 4m4-4 4 4M5 20h14"/></svg>';
  $okIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12.5 4.5 4.5L19 7.5"/></svg>';
  $infoIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>';
  $uploadField = function (string $model, string $title, string $hint, bool $required, $file) use ($docIcon) {
      return compact('model', 'title', 'hint', 'required', 'file');
  };
  $uploads = [
      $uploadField('icFront', 'IC / Pasport (depan)', 'Gambar jelas, semua sudut kelihatan', true, $icFront),
      $uploadField('icBack', 'IC (belakang)', 'Tidak perlu untuk pasport', false, $icBack),
      $uploadField('license', 'Lesen memandu', 'Lesen Malaysia atau IDP yang sah', true, $license),
      $uploadField('bill', 'Bil utiliti terkini', 'TNB, air atau internet — 3 bulan terakhir', true, $bill),
      $uploadField('otherDoc', 'Dokumen sokongan (pilihan)', 'Contoh: surat majikan, SSM / LOU syarikat', false, $otherDoc),
  ];
@endphp
<div class="sw-formpage">
  <section class="sw-formhero">
    <div class="sw-container">
      <p class="sw-eyebrow sw-eyebrow--light">Tempahan {{ $lead->referenceNumber() }}</p>
      <h1 class="sw-formhero__title">{{ $statusText }}</h1>
      <p class="sw-formhero__intro">Hai {{ $lead->full_name }}, halaman ini khas untuk tempahan anda. Simpan pautan ini untuk menyemak status pada bila-bila masa.</p>
    </div>
  </section>

  <div class="sw-container sw-formwrap">
    {{-- ============ SUMMARY ============ --}}
    <aside class="sw-formaside">
      <div class="sw-summary">
        <div class="sw-summary__head">
          <strong>{{ $lead->vehicleName() }}</strong>
          <span>{{ $lead->referenceNumber() }}</span>
        </div>
        <dl>
          <div><dt>Ambil</dt><dd>{{ $lead->pickupLabel() ?: '-' }}</dd></div>
          <div><dt>Pulang</dt><dd>{{ optional($lead->end_date)->format('d/m/Y') ?: '-' }}</dd></div>
          <div><dt>Lokasi</dt><dd>{{ $lead->pickupPlace() ?: '-' }}</dd></div>
          @if($lead->hasQuote())
            <div><dt>Tempoh</dt><dd>{{ $lead->rental_days }} hari</dd></div>
            @if($lead->quoted_list_daily > $lead->quoted_daily)
              <div><dt>Harga asal</dt><dd class="is-strike">{{ $rm($lead->quoted_list_daily) }}/hari</dd></div>
            @endif
            <div><dt>Harga tempahan</dt><dd>{{ $rm($lead->quoted_daily) }}/hari</dd></div>
            <div><dt>Sewa ({{ \App\Support\Pricing::PACKAGE_LABELS[$lead->quote_package] ?? 'Harian' }})</dt><dd>{{ $rm($lead->quoted_rental_total) }}</dd></div>
            <div><dt>Deposit (dikembalikan)</dt><dd>{{ $rm($lead->deposit_amount) }}</dd></div>
            @if($lead->delivery_fee)
              <div><dt>Penghantaran</dt><dd>{{ $rm($lead->delivery_fee) }}</dd></div>
            @endif
            <div class="is-total"><dt>Jumlah bayaran</dt><dd>{{ $rm($lead->amountDue()) }}</dd></div>
          @endif
        </dl>
      </div>
      <p class="sw-formaside__title">Perlu bantuan?</p>
      <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', (string) $settings->whatsapp_number) }}?text={{ rawurlencode('Salam, saya ingin bertanya tentang tempahan '.$lead->referenceNumber()) }}" target="_blank" rel="noopener" class="sw-btn sw-btn--whatsapp sw-btn--block">WhatsApp {{ $settings->picName() }}</a>
      <a href="{{ route('rental.terms') }}" target="_blank" rel="noopener" class="sw-btn sw-btn--ghost-dark sw-btn--block">Terma &amp; Syarat Sewaan</a>
    </aside>

    {{-- ============ MAIN ============ --}}
    <article class="sw-formcard">
      <div class="sw-portal">
        <div class="sw-status">
          <span @class(['sw-status__pill', 'sw-status__pill--ok' => $pillOk])>{{ strtoupper($lead->stageLabel()) }}</span>
          <span class="sw-status__ref">Rujukan <strong>{{ $lead->referenceNumber() }}</strong></span>
        </div>

        @unless(in_array($view, ['rejected', 'cancelled'], true))
          <ol class="sw-track" aria-label="Kemajuan tempahan">
            @foreach($steps as $i => $step)
              <li @class(['is-done' => $i < $current, 'is-current' => $i === $current])><span>{{ $step }}</span></li>
            @endforeach
          </ol>
        @endunless

        @if($justSubmitted)
          <div class="sw-notice sw-notice--ok" role="status">{!! $okIcon !!}<span>Terima kasih! Maklumat anda telah dihantar. PIC kami akan menyemak dan menghubungi anda melalui WhatsApp.</span></div>
        @endif

        {{-- ---------------- VERIFY ---------------- --}}
        @if($view === 'verify')
          <form wire:submit="submitVerification" class="sw-fstep" novalidate>
            <div class="sw-fstep__head">
              <h2>Semakan kelayakan penyewa</h2>
              <p>Lengkapkan butiran dan muat naik dokumen. Dokumen anda disimpan secara sulit dan hanya digunakan untuk semakan tempahan ini.</p>
            </div>

            <p class="sw-section-title">1 · Butiran penyewa</p>
            <div class="sw-grid sw-grid--2">
              <label class="sw-field"><span class="sw-field__label">Nama penuh (seperti dalam IC) <em>*</em></span>
                <input type="text" wire:model.blur="fullName" autocomplete="name">
                @error('fullName') <span class="sw-error">{{ $message }}</span> @enderror
              </label>
              <label class="sw-field"><span class="sw-field__label">No. IC / Pasport <em>*</em></span>
                <input type="text" wire:model.blur="icNumber" placeholder="900101-14-5678">
                @error('icNumber') <span class="sw-error">{{ $message }}</span> @enderror
              </label>
              <label class="sw-field"><span class="sw-field__label">Tarikh lahir <em>*</em></span>
                <input type="date" wire:model.blur="dateOfBirth" max="{{ now()->subYears(18)->format('Y-m-d') }}">
                @error('dateOfBirth') <span class="sw-error">{{ $message }}</span> @enderror
              </label>
              <label class="sw-field"><span class="sw-field__label">No. telefon <em>*</em></span>
                <input type="tel" wire:model.blur="phone" inputmode="tel" autocomplete="tel">
                @error('phone') <span class="sw-error">{{ $message }}</span> @enderror
              </label>
              <label class="sw-field"><span class="sw-field__label">E-mel <em>*</em></span>
                <input type="email" wire:model.blur="email" autocomplete="email">
                @error('email') <span class="sw-error">{{ $message }}</span> @enderror
              </label>
              <label class="sw-field"><span class="sw-field__label">Media sosial (pilihan)</span>
                <input type="text" wire:model.blur="socialMedia" placeholder="Pautan Facebook / Instagram / LinkedIn">
                @error('socialMedia') <span class="sw-error">{{ $message }}</span> @enderror
              </label>
            </div>
            <label class="sw-field"><span class="sw-field__label">Alamat tempat tinggal semasa <em>*</em></span>
              <textarea wire:model.blur="homeAddress" rows="3" autocomplete="street-address" placeholder="Alamat penuh seperti pada bil utiliti"></textarea>
              @error('homeAddress') <span class="sw-error">{{ $message }}</span> @enderror
            </label>
            <div class="sw-grid sw-grid--2">
              <label class="sw-field"><span class="sw-field__label">Pekerjaan <em>*</em></span>
                <input type="text" wire:model.blur="occupation" placeholder="Contoh: Jurutera">
                @error('occupation') <span class="sw-error">{{ $message }}</span> @enderror
              </label>
              <label class="sw-field"><span class="sw-field__label">Majikan / syarikat <em>*</em></span>
                <input type="text" wire:model.blur="employer" placeholder="Nama majikan atau perniagaan sendiri">
                @error('employer') <span class="sw-error">{{ $message }}</span> @enderror
              </label>
              <label class="sw-field"><span class="sw-field__label">No. lesen memandu <em>*</em></span>
                <input type="text" wire:model.blur="licenseNumber">
                @error('licenseNumber') <span class="sw-error">{{ $message }}</span> @enderror
              </label>
              <label class="sw-field"><span class="sw-field__label">Tarikh luput lesen <em>*</em></span>
                <input type="date" wire:model.blur="licenseExpiry">
                @error('licenseExpiry') <span class="sw-error">{{ $message }}</span> @enderror
              </label>
            </div>

            <p class="sw-section-title">2 · Waris terdekat</p>
            <div class="sw-grid sw-grid--3">
              <label class="sw-field"><span class="sw-field__label">Nama waris <em>*</em></span>
                <input type="text" wire:model.blur="emergencyName">
                @error('emergencyName') <span class="sw-error">{{ $message }}</span> @enderror
              </label>
              <label class="sw-field"><span class="sw-field__label">Hubungan <em>*</em></span>
                <input type="text" wire:model.blur="emergencyRelation" placeholder="Ibu / Suami / Adik">
                @error('emergencyRelation') <span class="sw-error">{{ $message }}</span> @enderror
              </label>
              <label class="sw-field"><span class="sw-field__label">No. telefon waris <em>*</em></span>
                <input type="tel" wire:model.blur="emergencyPhone" inputmode="tel">
                @error('emergencyPhone') <span class="sw-error">{{ $message }}</span> @enderror
              </label>
            </div>

            <p class="sw-section-title">3 · Dokumen</p>
            <div class="sw-grid sw-grid--2">
              @foreach($uploads as $u)
                <div class="sw-upload" wire:key="up-{{ $u['model'] }}">
                  <label @class(['sw-upload__box', 'is-filled' => $u['file']])>
                    <input type="file" wire:model="{{ $u['model'] }}" accept="image/*,.pdf,.heic,.heif" aria-label="{{ $u['title'] }}">
                    <span class="sw-upload__icon">{!! $u['file'] ? $okIcon : $docIcon !!}</span>
                    <span class="sw-upload__text">
                      <strong>{{ $u['title'] }}@if($u['required']) <em style="color:var(--red);font-style:normal">*</em>@endif</strong>
                      <span>{{ $u['file'] ? $u['file']->getClientOriginalName() : $u['hint'] }}</span>
                    </span>
                  </label>
                  <span class="sw-upload__progress" wire:loading wire:target="{{ $u['model'] }}">Memuat naik…</span>
                  @error($u['model']) <span class="sw-error">{{ $message }}</span> @enderror
                </div>
              @endforeach
            </div>
            <p class="sw-hint">Format JPG, PNG, HEIC atau PDF, maksimum 10MB setiap fail.</p>

            <p class="sw-section-title">4 · Terma &amp; Syarat</p>
            <div class="sw-termsbox sw-terms--compact" tabindex="0" aria-label="Terma dan syarat sewaan">
              @include('partials.rental-terms')
            </div>
            <div class="sw-grid">
              @foreach([
                'agreeTerms' => 'Saya telah membaca dan bersetuju dengan Terma & Syarat Sewaan SEWOLAH.',
                'agreeFuel' => 'Saya faham polisi minyak Full to Full: kereta diserah penuh dan saya pulangkan penuh, kekurangan akan dicaj.',
                'agreeDeposit' => 'Saya faham deposit '.$rm($lead->deposit_amount).' dikembalikan dalam '.\App\Support\Pricing::setting('deposit_refund_days').' hari selepas pemeriksaan, tertakluk kepada tuntutan sah.',
                'agreeCancel' => 'Saya faham polisi pembatalan: kurang 72 jam sebelum ambil, 1 hari sewa ditolak.',
                'agreeUsage' => 'Saya akan menggunakan kenderaan dalam lokasi & had mileage yang dipersetujui, dan tidak menyalahgunakannya.',
              ] as $model => $text)
                <div>
                  <label class="sw-consent-check"><input type="checkbox" wire:model="{{ $model }}"><span>{{ $text }}</span></label>
                  @error($model) <span class="sw-error">{{ $message }}</span> @enderror
                </div>
              @endforeach
            </div>
            <label class="sw-field"><span class="sw-field__label">Tandatangan digital — taip nama penuh anda <em>*</em></span>
              <input type="text" wire:model.blur="signature" placeholder="{{ $lead->full_name }}" style="font-style:italic">
              @error('signature') <span class="sw-error">{{ $message }}</span> @enderror
            </label>

            @if($errors->any())
              <div class="sw-notice sw-notice--bad" role="alert">{!! $infoIcon !!}<span>Sila semak ruangan yang bertanda merah.</span></div>
            @endif

            <div class="sw-factions">
              <button type="submit" class="sw-btn sw-btn--red sw-btn--lg sw-btn--grow" wire:loading.attr="disabled" wire:target="submitVerification,icFront,icBack,license,bill,otherDoc">
                <span wire:loading.remove wire:target="submitVerification">Hantar untuk semakan</span>
                <span wire:loading wire:target="submitVerification">Menghantar…</span>
              </button>
            </div>
          </form>

        {{-- ---------------- REVIEWING ---------------- --}}
        @elseif($view === 'reviewing')
          <div class="sw-notice">{!! $infoIcon !!}<span>Dokumen anda telah diterima pada {{ optional($lead->verification_submitted_at)->format('d/m/Y, H:i') }} dan sedang disemak oleh PIC. Anda akan menerima pautan bayaran melalui WhatsApp & e-mel sebaik sahaja kelayakan diluluskan.</span></div>
          <ul class="sw-checklist">
            <li>Butiran penyewa & waris diterima</li>
            <li>Dokumen IC, lesen & bil utiliti diterima</li>
            <li>Terma & Syarat diterima ({{ $lead->terms_signature }})</li>
          </ul>

        {{-- ---------------- PAY ---------------- --}}
        @elseif($view === 'pay')
          <form wire:submit="submitPayment" class="sw-fstep" novalidate>
            <div class="sw-fstep__head">
              <h2>Bayaran tempahan</h2>
              <p>Kelayakan anda telah diluluskan. Buat bayaran penuh dan muat naik resit untuk mengesahkan tempahan.</p>
            </div>
            <div class="sw-summary">
              <dl>
                <div><dt>Sewa {{ $lead->rental_days }} hari</dt><dd>{{ $rm($lead->quoted_rental_total) }}</dd></div>
                <div><dt>Deposit keselamatan (dikembalikan)</dt><dd>{{ $rm($lead->deposit_amount) }}</dd></div>
                @if($lead->delivery_fee)<div><dt>Caj penghantaran</dt><dd>{{ $rm($lead->delivery_fee) }}</dd></div>@endif
                <div class="is-total"><dt>Jumlah perlu dibayar</dt><dd>{{ $rm($lead->amountDue()) }}</dd></div>
              </dl>
            </div>

            @if($settings->hasBankDetails() || $settings->qrUrl())
              <div @class(['sw-paybox', 'sw-paybox--qr' => $settings->qrUrl()])>
                <div style="display:grid;gap:14px">
                  @if($settings->hasBankDetails())
                    <div class="sw-paybox__row"><span>Bank</span><strong>{{ $settings->bank_name }}</strong></div>
                    <div class="sw-paybox__row"><span>No. akaun</span><strong>{{ $settings->bank_account_number }} <button type="button" class="sw-copy" x-data x-on:click="navigator.clipboard?.writeText('{{ preg_replace('/[^0-9]/', '', $settings->bank_account_number) }}'); $el.textContent='Disalin'">Salin</button></strong></div>
                    @if($settings->bank_account_name)<div class="sw-paybox__row"><span>Nama akaun</span><strong>{{ $settings->bank_account_name }}</strong></div>@endif
                  @endif
                  <div class="sw-paybox__row"><span>Rujukan bayaran</span><strong>{{ $lead->referenceNumber() }}</strong></div>
                </div>
                @if($settings->qrUrl())
                  <div class="sw-paybox__qr"><img src="{{ $settings->qrUrl() }}" alt="Kod QR DuitNow SEWOLAH" width="300" height="300" loading="lazy"><p class="sw-hint" style="margin:6px 0 0">Imbas DuitNow QR</p></div>
                @endif
              </div>
            @else
              <div class="sw-notice">{!! $infoIcon !!}<span>Butiran akaun bayaran akan dihantar oleh PIC melalui WhatsApp.</span></div>
            @endif

            <div class="sw-upload">
              <label @class(['sw-upload__box', 'is-filled' => $receipt])>
                <input type="file" wire:model="receipt" accept="image/*,.pdf,.heic,.heif" aria-label="Resit bayaran">
                <span class="sw-upload__icon">{!! $receipt ? $okIcon : $docIcon !!}</span>
                <span class="sw-upload__text"><strong>Resit bayaran <em style="color:var(--red);font-style:normal">*</em></strong><span>{{ $receipt ? $receipt->getClientOriginalName() : 'Tangkapan skrin atau PDF resit pindahan' }}</span></span>
              </label>
              <span class="sw-upload__progress" wire:loading wire:target="receipt">Memuat naik…</span>
              @error('receipt') <span class="sw-error">{{ $message }}</span> @enderror
            </div>
            <label class="sw-field"><span class="sw-field__label">No. rujukan transaksi (pilihan)</span>
              <input type="text" wire:model.blur="paymentReference" placeholder="Contoh: rujukan DuitNow / FPX">
            </label>
            <div class="sw-factions">
              <button type="submit" class="sw-btn sw-btn--red sw-btn--lg sw-btn--grow" wire:loading.attr="disabled" wire:target="submitPayment,receipt">
                <span wire:loading.remove wire:target="submitPayment">Hantar resit bayaran</span>
                <span wire:loading wire:target="submitPayment">Menghantar…</span>
              </button>
            </div>
          </form>

        {{-- ---------------- PAYMENT REVIEW ---------------- --}}
        @elseif($view === 'payment_review')
          <div class="sw-notice">{!! $infoIcon !!}<span>Resit bayaran {{ $rm($lead->payment_amount) }} diterima pada {{ optional($lead->payment_submitted_at)->format('d/m/Y, H:i') }}. PIC sedang mengesahkan bayaran — anda akan menerima pengesahan tempahan melalui WhatsApp & e-mel.</span></div>

        {{-- ---------------- CONFIRMED ---------------- --}}
        @elseif($view === 'confirmed')
          <div class="sw-notice sw-notice--ok">{!! $okIcon !!}<span>Tempahan anda <strong>disahkan</strong>. Bayaran {{ $rm($lead->payment_amount ?: $lead->amountDue()) }} telah diterima.</span></div>
          <p class="sw-section-title">Sila bawa semasa mengambil kereta</p>
          <ul class="sw-checklist">
            <li>IC / pasport <strong>asal</strong></li>
            <li>Lesen memandu <strong>asal</strong> yang sah</li>
            <li>Staf operasi akan menghubungi anda 3–6 jam sebelum serahan</li>
            <li>Pemeriksaan kereta dibuat bersama dan dirakam semasa serahan</li>
            <li>Kereta diserah dengan tangki penuh — pulangkan penuh (Full to Full)</li>
          </ul>

        {{-- ---------------- COMPLETED ---------------- --}}
        @elseif($view === 'completed')
          <div class="sw-notice sw-notice--ok">{!! $okIcon !!}<span>Terima kasih kerana menyewa bersama SEWOLAH. @if($lead->deposit_refunded_at) Deposit anda telah dipulangkan pada {{ $lead->deposit_refunded_at->format('d/m/Y') }}. @else Deposit anda akan dipulangkan dalam tempoh {{ \App\Support\Pricing::setting('deposit_refund_days') }} hari selepas pemeriksaan. @endif</span></div>

        @elseif($view === 'rejected')
          <div class="sw-notice sw-notice--bad">{!! $infoIcon !!}<span>Dukacita dimaklumkan permohonan ini tidak dapat diteruskan. Sebarang bayaran yang telah dibuat akan dikembalikan sepenuhnya. Hubungi kami melalui WhatsApp untuk maklumat lanjut.</span></div>

        @else
          <div class="sw-notice sw-notice--bad">{!! $infoIcon !!}<span>Tempahan ini telah dibatalkan. Hubungi kami melalui WhatsApp jika anda ingin membuat tempahan baharu.</span></div>
        @endif
      </div>
    </article>
  </div>
</div>
