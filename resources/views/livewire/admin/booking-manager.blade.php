@php
  $rm = fn ($v) => \App\Support\Pricing::rm($v);
  $phases = $compliance['phases'];
  $statusClass = \App\Support\BookingSop::STATUS_FOR_STAGE[$lead->stage] ?? 'baru';
  $meterClass = fn ($p) => $p >= 100 ? '' : ($p >= 50 ? 'adm-meter--warn' : 'adm-meter--bad');
  $sentTemplates = $lead->events->pluck('type')->filter(fn ($t) => str_starts_with($t, 'whatsapp_'))->map(fn ($t) => substr($t, 9))->unique()->all();
  $next = match ($lead->stage) {
      'permohonan' => 'quote',
      'tawaran' => 'verification',
      'kelayakan' => 'verification',
      'bayaran' => 'approved',
      'disahkan' => in_array('confirmed', $sentTemplates, true) ? 'handover' : 'confirmed',
      'diserah' => 'return',
      'dipulang', 'selesai' => 'thanks',
      'ditolak' => 'rejected',
      default => null,
  };
  $waIcon = '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2Zm5.3 14.1c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .2-3.3-.7-2.8-1.1-4.5-3.9-4.7-4.1-.1-.2-1.1-1.5-1.1-2.8 0-1.4.7-2 1-2.3.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.8 2c.1.2.1.4 0 .5l-.3.5-.4.4c-.1.2-.3.3-.1.6.2.3.8 1.3 1.7 2.1 1.2 1 2.1 1.3 2.4 1.5.3.1.5.1.6-.1l.9-1c.2-.3.4-.2.6-.1l1.9.9c.3.1.5.2.5.3.1.2.1.7-.1 1.4Z"/></svg>';
  $phaseOpen = fn ($key) => $phases[$key]['active'];
  $docs = $lead->documents->groupBy('type');
  $docUrl = fn ($d) => route('admin.bookings.document', [$lead, $d]);
  $fuel = \App\Livewire\Admin\BookingManager::FUEL_LEVELS;
@endphp
<div class="adm-grid" style="gap:20px">
  <div class="adm-head">
    <div>
      <p style="margin:0 0 6px"><a href="{{ route('admin.bookings') }}" class="adm-hint" style="text-decoration:none">← Semua tempahan</a></p>
      <h1>{{ $lead->referenceNumber() }} · {{ $lead->full_name }}</h1>
      <p>{{ $lead->vehicleName() }} · Ambil {{ $lead->pickupLabel() ?: '-' }} · {{ $lead->pickupPlace() ?: '-' }}</p>
    </div>
    <div class="adm-head__actions">
      <span class="adm-pill adm-pill--{{ $statusClass }}">{{ $lead->stageLabel() }}</span>
      <span class="adm-score" title="Item SOP selesai bagi fasa yang telah bermula">Pematuhan SOP <strong>{{ $compliance['percent'] }}%</strong> {{ $compliance['done'] }}/{{ $compliance['total'] }}</span>
    </div>
  </div>

  @if($flash)
    <div @class(['adm-alert', 'adm-alert--success' => $flashType === 'success', 'adm-alert--error' => $flashType === 'error']) role="status">
      <span>{{ $flash }}</span>
      <button type="button" wire:click="dismissFlash" aria-label="Tutup">✕</button>
    </div>
  @endif

  {{-- Phase overview --}}
  <section class="adm-card">
    <div class="adm-phases">
      @foreach($phases as $key => $p)
        <a href="#fasa-{{ $key }}" @class(['adm-phase', 'is-inactive' => ! $p['active'], 'is-complete' => $p['percent'] >= 100])>
          <span class="adm-phase__top"><span>{{ $loop->iteration }}. {{ $p['label'] }}</span><span class="adm-phase__num">{{ $p['done'] }}/{{ $p['total'] }}</span></span>
          <span class="adm-meter {{ $p['active'] ? $meterClass($p['percent']) : '' }}"><span style="width:{{ $p['percent'] }}%"></span></span>
        </a>
      @endforeach
    </div>
  </section>

  <div class="adm-mgr">
    <div class="adm-mgr__main">

      {{-- 1. TEMPAHAN & TAWARAN --}}
      <section class="adm-card adm-phasecard" id="fasa-tempahan">
        <div class="adm-card__head">
          <div><h2 class="adm-card__title">1. Tempahan &amp; Tawaran Harga</h2><p class="adm-card__sub">Pilih kereta daripada senarai harga, semak kekosongan, hantar tawaran.</p></div>
        </div>
        <div class="adm-phasecard__grid adm-phasecard__grid--2">
          <div style="display:grid;gap:14px">
            <form wire:submit="saveQuote" class="adm-fgrid">
              <label class="adm-field"><span class="adm-label">Kereta (senarai harga) <em>*</em></span>
                <select wire:model.live="carRateId" class="adm-select">
                  <option value="">— Pilih kereta —</option>
                  @foreach($rates as $brand => $group)
                    <optgroup label="{{ $brand }}">
                      @foreach($group as $r)
                        <option value="{{ $r->id }}">{{ $r->model }} — {{ $rm($r->bookingDaily()) }}/hari{{ $r->is_active ? '' : ' (tidak aktif)' }}</option>
                      @endforeach
                    </optgroup>
                  @endforeach
                </select>
                @if($lead->vehicle_name_snapshot && ! $lead->car_rate_id)<span class="adm-hint">Pilihan pelanggan: {{ $lead->vehicle_name_snapshot }}</span>@endif
                @error('carRateId') <span class="adm-error">{{ $message }}</span> @enderror
              </label>
              <div class="adm-fgrid adm-fgrid--2">
                <label class="adm-field"><span class="adm-label">Harga booking / hari (RM) <em>*</em></span>
                  <input type="number" min="1" wire:model.live.debounce.400ms="quotedDaily" class="adm-input" inputmode="numeric">
                  @error('quotedDaily') <span class="adm-error">{{ $message }}</span> @enderror
                </label>
                <label class="adm-field"><span class="adm-label">Caj penghantaran (RM)</span>
                  <input type="number" min="0" wire:model.live.debounce.400ms="deliveryFee" class="adm-input" inputmode="numeric" placeholder="0">
                  @error('deliveryFee') <span class="adm-error">{{ $message }}</span> @enderror
                </label>
              </div>
              <div class="adm-btnrow"><button type="submit" class="adm-btn adm-btn--primary">Simpan tawaran</button>
                @if($lead->quote_sent_at)<span class="adm-hint">Tawaran dihantar {{ $lead->quote_sent_at->format('d/m/Y H:i') }}</span>@endif
              </div>
            </form>

            @if($preview)
              <div class="adm-quote">
                <div><span>Tempoh</span><strong>{{ $preview['days'] }} hari · {{ $preview['package'] }}</strong></div>
                @if($preview['list'] > (int) $quotedDaily)<div><span>Harga asal</span><strong class="is-strike">{{ $rm($preview['list']) }}/hari</strong></div>@endif
                <div><span>Harga booking</span><strong>{{ $rm((int) $quotedDaily) }}/hari</strong></div>
                <div><span>Jumlah sewa</span><strong>{{ $rm($preview['total']) }}</strong></div>
                <div><span>Deposit</span><strong>{{ $rm($preview['deposit']) }}</strong></div>
                <div class="is-total"><span>Jumlah perlu dibayar</span><strong>{{ $rm($preview['due']) }}</strong></div>
                @if($preview['agent'])
                  <div class="is-internal"><span>Harga ejen / VIP <span class="adm-badge-int">DALAMAN</span></span><strong>{{ $rm($preview['agent']) }} / {{ $rm($preview['vip']) }}</strong></div>
                  <div class="is-internal"><span>Anggaran margin (vs ejen)</span><strong>{{ $rm($preview['margin']) }}</strong></div>
                @endif
              </div>
            @endif
          </div>
          <div>
            <p class="adm-subhead">SOP fasa ini</p>
            @include('livewire.admin.partials.sop-list', ['items' => $phases['tempahan']['items']])
          </div>
        </div>
      </section>

      {{-- 2. KELAYAKAN --}}
      <section @class(['adm-card', 'adm-phasecard', 'is-inactive' => ! $phaseOpen('kelayakan')]) id="fasa-kelayakan">
        <div class="adm-card__head">
          <div><h2 class="adm-card__title">2. Semakan Kelayakan</h2><p class="adm-card__sub">Pautan borang pelanggan: <a href="{{ $lead->portalUrl() }}" target="_blank" rel="noopener" style="color:var(--accent-ink);font-weight:700">buka portal ↗</a></p></div>
          @if($lead->verification_submitted_at)<span class="adm-hint">Dihantar {{ $lead->verification_submitted_at->format('d/m/Y H:i') }}</span>@endif
        </div>
        <div class="adm-phasecard__grid adm-phasecard__grid--2">
          <div style="display:grid;gap:16px">
            @if($lead->verification_submitted_at || $lead->ic_number)
              <dl class="adm-kv">
                <div><dt>No. IC / Pasport</dt><dd class="adm-mono">{{ $lead->ic_number ?: '-' }}</dd></div>
                <div><dt>Tarikh lahir</dt><dd>{{ optional($lead->date_of_birth)->format('d/m/Y') ?: '-' }}@if($lead->date_of_birth) ({{ $lead->date_of_birth->age }} tahun)@endif</dd></div>
                <div><dt>Alamat</dt><dd>{!! nl2br(e($lead->home_address ?: '-')) !!}</dd></div>
                <div><dt>Pekerjaan</dt><dd>{{ $lead->occupation ?: '-' }} · {{ $lead->employer ?: '-' }}</dd></div>
                <div><dt>Lesen</dt><dd>{{ $lead->license_number ?: '-' }} · luput {{ optional($lead->license_expiry)->format('d/m/Y') ?: '-' }}</dd></div>
                <div><dt>Waris</dt><dd>{{ $lead->emergency_name ?: '-' }} ({{ $lead->emergency_relation ?: '-' }}) · <a href="tel:{{ $lead->emergency_phone }}" style="color:inherit">{{ $lead->emergency_phone ?: '-' }}</a></dd></div>
                <div><dt>Media sosial</dt><dd>{{ $lead->social_media ?: '-' }}</dd></div>
                <div><dt>T&amp;C</dt><dd>{{ $lead->terms_signature ?: '-' }}@if($lead->terms_accepted_at) · {{ $lead->terms_accepted_at->format('d/m/Y H:i') }} · IP {{ $lead->terms_ip }}@endif</dd></div>
              </dl>
            @else
              <div class="adm-callout">Pelanggan belum menghantar borang kelayakan. Selepas pelanggan <strong>SETUJU</strong> dengan harga, hantar WhatsApp <strong>"Pautan semakan kelayakan"</strong> di panel kanan.</div>
            @endif

            @php $kycDocs = $lead->documents->where('type', '!=', 'receipt'); @endphp
            @if($kycDocs->isNotEmpty())
              <div>
                <p class="adm-subhead">Dokumen</p>
                <div class="adm-docs">
                  @foreach($kycDocs as $d)
                    <a href="{{ $docUrl($d) }}" target="_blank" rel="noopener" class="adm-doc">
                      <span class="adm-doc__thumb">@if($d->isImage() && ! str_contains((string) $d->mime, 'heic'))<img src="{{ $docUrl($d) }}" alt="{{ $d->label() }}" loading="lazy">@else{{ strtoupper(pathinfo($d->path, PATHINFO_EXTENSION)) }}@endif</span>
                      <span class="adm-doc__label">{{ $d->label() }}</span>
                      <span class="adm-doc__meta">{{ number_format(($d->size ?? 0) / 1024) }} KB · {{ $d->created_at->format('d/m H:i') }}</span>
                    </a>
                  @endforeach
                </div>
              </div>
            @endif

            @if($lead->stage === 'semakan')
              <div class="adm-fgrid">
                <label class="adm-field"><span class="adm-label">Catatan keputusan (dalaman)</span>
                  <textarea wire:model="decisionNote" class="adm-textarea" rows="2" placeholder="Contoh: semakan PSS bersih, waris disahkan / sebab tidak lulus / dokumen yang perlu dihantar semula"></textarea>
                  @error('decisionNote') <span class="adm-error">{{ $message }}</span> @enderror
                </label>
                <div class="adm-btnrow">
                  <button type="button" wire:click="approveVerification" class="adm-btn adm-btn--primary">Luluskan kelayakan</button>
                  <button type="button" wire:click="requestNewDocuments" class="adm-btn">Minta dokumen semula</button>
                  <button type="button" wire:click="rejectVerification" wire:confirm="Tandakan permohonan ini TIDAK LULUS?" class="adm-btn adm-btn--danger">Tidak lulus</button>
                </div>
              </div>
            @elseif($lead->verification_approved_at)
              <div class="adm-callout">Diluluskan {{ $lead->verification_approved_at->format('d/m/Y H:i') }}@if($lead->verification_note) — {{ $lead->verification_note }}@endif</div>
            @elseif($lead->stage === 'ditolak')
              <div class="adm-callout">Tidak lulus — {{ $lead->verification_note }}</div>
            @endif
          </div>
          <div>
            <p class="adm-subhead">SOP semakan (wajib sebelum lulus)</p>
            @include('livewire.admin.partials.sop-list', ['items' => $phases['kelayakan']['items']])
          </div>
        </div>
      </section>

      {{-- 3 & 4. PENGESAHAN & BAYARAN --}}
      <section @class(['adm-card', 'adm-phasecard', 'is-inactive' => ! $phaseOpen('pengesahan')]) id="fasa-pengesahan">
        <div class="adm-card__head">
          <div><h2 class="adm-card__title">3. Pengesahan Tempahan &amp; 4. Bayaran</h2><p class="adm-card__sub">Jumlah: sewa + deposit{{ $lead->delivery_fee ? ' + penghantaran' : '' }} = <strong>{{ $rm($lead->amountDue()) }}</strong></p></div>
          @if($lead->payment_verified_at)<span class="adm-pill adm-pill--disahkan">Bayaran disahkan</span>@elseif($lead->payment_submitted_at)<span class="adm-pill adm-pill--dihubungi">Resit diterima</span>@endif
        </div>
        <div class="adm-phasecard__grid adm-phasecard__grid--2">
          <div style="display:grid;gap:14px">
            @php $receipts = $docs->get('receipt', collect()); @endphp
            @if($receipts->isNotEmpty())
              <div class="adm-docs">
                @foreach($receipts as $d)
                  <a href="{{ $docUrl($d) }}" target="_blank" rel="noopener" class="adm-doc">
                    <span class="adm-doc__thumb">@if($d->isImage() && ! str_contains((string) $d->mime, 'heic'))<img src="{{ $docUrl($d) }}" alt="Resit" loading="lazy">@else{{ strtoupper(pathinfo($d->path, PATHINFO_EXTENSION)) }}@endif</span>
                    <span class="adm-doc__label">Resit {{ $rm($lead->payment_amount) }}</span>
                    <span class="adm-doc__meta">{{ $lead->payment_reference ?: 'Tiada rujukan' }} · {{ $d->created_at->format('d/m H:i') }}</span>
                  </a>
                @endforeach
              </div>
            @endif
            @if($lead->stage === 'bayaran')
              <div class="adm-callout">Semak akaun bank untuk <strong>{{ $rm($lead->amountDue()) }}</strong> dengan rujukan <strong>{{ $lead->referenceNumber() }}</strong> sebelum mengesahkan.</div>
              <div class="adm-btnrow">
                <button type="button" wire:click="verifyPayment" wire:confirm="Sahkan bayaran {{ $rm($lead->amountDue()) }} telah diterima dalam akaun?" class="adm-btn adm-btn--primary">Sahkan bayaran diterima</button>
                @unless($lead->payment_submitted_at)<span class="adm-hint">Resit belum dimuat naik — boleh sahkan jika bayaran diterima terus.</span>@endunless
              </div>
            @elseif($lead->payment_verified_at)
              <div class="adm-callout">Bayaran <strong>{{ $rm($lead->payment_amount) }}</strong> disahkan {{ $lead->payment_verified_at->format('d/m/Y H:i') }}.</div>
            @endif
          </div>
          <div style="display:grid;gap:14px">
            <div><p class="adm-subhead">SOP pengesahan</p>@include('livewire.admin.partials.sop-list', ['items' => $phases['pengesahan']['items']])</div>
            <div><p class="adm-subhead">SOP bayaran</p>@include('livewire.admin.partials.sop-list', ['items' => $phases['bayaran']['items']])</div>
          </div>
        </div>
      </section>

      {{-- 5. SERAHAN --}}
      <section @class(['adm-card', 'adm-phasecard', 'is-inactive' => ! $phaseOpen('serahan')]) id="fasa-serahan">
        <div class="adm-card__head">
          <div><h2 class="adm-card__title">5. Serah Kereta</h2><p class="adm-card__sub">Ikut SOP serahan: dokumen asal, video, minyak penuh, VUA dengan Remark.</p></div>
          @if($lead->handover_at)<span class="adm-hint">Diserah {{ $lead->handover_at->format('d/m/Y H:i') }}</span>@endif
        </div>
        <div class="adm-phasecard__grid adm-phasecard__grid--2">
          <form wire:submit="recordHandover" class="adm-fgrid">
            <div class="adm-fgrid adm-fgrid--2">
              <label class="adm-field"><span class="adm-label">Odometer serahan (km) <em>*</em></span>
                <input type="number" min="0" wire:model="mileageOut" class="adm-input" inputmode="numeric" @disabled($lead->handover_at)>
                @error('mileageOut') <span class="adm-error">{{ $message }}</span> @enderror
              </label>
              <label class="adm-field"><span class="adm-label">Paras minyak <em>*</em></span>
                <select wire:model="fuelOut" class="adm-select" @disabled($lead->handover_at)>@foreach($fuel as $f)<option>{{ $f }}</option>@endforeach</select>
              </label>
            </div>
            <label class="adm-field"><span class="adm-label">Lokasi penggunaan dibenarkan (Remark VUA) <em>*</em></span>
              <input type="text" wire:model="usageArea" class="adm-input" placeholder="Contoh: Lembah Klang & Selangor sahaja" @disabled($lead->handover_at)>
              @error('usageArea') <span class="adm-error">{{ $message }}</span> @enderror
            </label>
            <div class="adm-fgrid adm-fgrid--2">
              <label class="adm-field"><span class="adm-label">Had mileage (km)</span>
                <input type="number" min="0" wire:model="mileageLimit" class="adm-input" placeholder="Contoh: 300/hari × tempoh" @disabled($lead->handover_at)>
              </label>
              <label class="adm-field"><span class="adm-label">Caj per km lebihan (RM)</span>
                <input type="number" min="0" wire:model="excessKmRate" class="adm-input" @disabled($lead->handover_at)>
              </label>
            </div>
            @unless($lead->handover_at)
              <div class="adm-btnrow"><button type="submit" class="adm-btn adm-btn--primary" @disabled($lead->stage !== 'disahkan')>Rekod serahan kereta</button>
                @if($lead->stage !== 'disahkan')<span class="adm-hint">Tersedia selepas bayaran disahkan.</span>@endif
              </div>
            @endunless
          </form>
          <div><p class="adm-subhead">SOP serahan</p>@include('livewire.admin.partials.sop-list', ['items' => $phases['serahan']['items']])</div>
        </div>
      </section>

      {{-- 6. PULANGAN --}}
      <section @class(['adm-card', 'adm-phasecard', 'is-inactive' => ! $phaseOpen('pulangan')]) id="fasa-pulangan">
        <div class="adm-card__head">
          <div><h2 class="adm-card__title">6. Pulang Kereta &amp; Deposit</h2>
            <p class="adm-card__sub">@if($lead->returned_at)Dipulang {{ $lead->returned_at->format('d/m/Y H:i') }} · deposit perlu dipulangkan sebelum <strong>{{ $lead->depositRefundDue()->format('d/m/Y') }}</strong>@else Semak bersama, rakam video, semak minyak & mileage.@endif</p></div>
          @if($lead->deposit_refunded_at)<span class="adm-pill adm-pill--disahkan">Deposit dipulangkan</span>@elseif($lead->returned_at && now()->gt($lead->depositRefundDue()))<span class="adm-pill" style="background:color-mix(in srgb,var(--bad) 14%,transparent);color:var(--bad)">Deposit lewat</span>@endif
        </div>
        <div class="adm-phasecard__grid adm-phasecard__grid--2">
          <div style="display:grid;gap:16px">
            <form wire:submit="recordReturn" class="adm-fgrid">
              <div class="adm-fgrid adm-fgrid--2">
                <label class="adm-field"><span class="adm-label">Odometer pulangan (km) <em>*</em></span>
                  <input type="number" min="0" wire:model="mileageIn" class="adm-input" inputmode="numeric" @disabled($lead->returned_at)>
                  @error('mileageIn') <span class="adm-error">{{ $message }}</span> @enderror
                </label>
                <label class="adm-field"><span class="adm-label">Paras minyak <em>*</em></span>
                  <select wire:model="fuelIn" class="adm-select" @disabled($lead->returned_at)>@foreach($fuel as $f)<option>{{ $f }}</option>@endforeach</select>
                </label>
              </div>
              @if($lead->returned_at && $lead->mileage_out !== null)
                <div class="adm-callout">Jarak digunakan: <strong>{{ number_format($lead->mileage_in - $lead->mileage_out) }} km</strong>@if($lead->mileage_limit) (had {{ number_format($lead->mileage_limit) }} km{{ $lead->mileage_in - $lead->mileage_out > $lead->mileage_limit && $lead->excess_km_rate ? ' — lebihan '.number_format($lead->mileage_in - $lead->mileage_out - $lead->mileage_limit).' km × '.$rm($lead->excess_km_rate).' = '.$rm(($lead->mileage_in - $lead->mileage_out - $lead->mileage_limit) * $lead->excess_km_rate) : '' }})@endif · minyak {{ $lead->fuel_out }} → {{ $lead->fuel_in }}</div>
              @endif
              @unless($lead->returned_at)
                <div class="adm-btnrow"><button type="submit" class="adm-btn adm-btn--primary" @disabled($lead->stage !== 'diserah')>Rekod pulangan kereta</button></div>
              @endunless
            </form>

            @if($lead->returned_at)
              <form wire:submit="refundDeposit" class="adm-fgrid">
                <div class="adm-fgrid adm-fgrid--2">
                  <label class="adm-field"><span class="adm-label">Potongan deposit (RM)</span>
                    <input type="number" min="0" max="{{ (int) $lead->deposit_amount }}" wire:model="depositDeduction" class="adm-input" placeholder="0" @disabled($lead->deposit_refunded_at)>
                    @error('depositDeduction') <span class="adm-error">{{ $message }}</span> @enderror
                  </label>
                  <label class="adm-field"><span class="adm-label">Sebab potongan (dengan bukti)</span>
                    <input type="text" wire:model="deductionReason" class="adm-input" placeholder="Contoh: kurang minyak 2 bar" @disabled($lead->deposit_refunded_at)>
                    @error('deductionReason') <span class="adm-error">{{ $message }}</span> @enderror
                  </label>
                </div>
                <p class="adm-hint">Deposit {{ $rm($lead->deposit_amount) }}. Saman lewat dituntut melalui invois berasingan — jangan tahan deposit (NOTA 109).</p>
                @unless($lead->deposit_refunded_at)
                  <div class="adm-btnrow"><button type="submit" class="adm-btn adm-btn--primary">Rekod deposit dipulangkan</button></div>
                @else
                  <div class="adm-callout">Deposit {{ $rm((int) $lead->deposit_amount - (int) $lead->deposit_deduction) }} dipulangkan {{ $lead->deposit_refunded_at->format('d/m/Y') }}.</div>
                @endunless
              </form>
            @endif
          </div>
          <div><p class="adm-subhead">SOP pulangan</p>@include('livewire.admin.partials.sop-list', ['items' => $phases['pulangan']['items']])</div>
        </div>
      </section>
    </div>

    {{-- SIDE --}}
    <aside class="adm-mgr__side">
      <section class="adm-card">
        <h2 class="adm-card__title" style="margin-bottom:4px">Template WhatsApp</h2>
        <p class="adm-card__sub" style="margin-bottom:14px">Klik untuk buka WhatsApp pelanggan dengan mesej siap. Langkah SOP direkod automatik.</p>
        <div class="adm-wa">
          @foreach(\App\Support\WhatsAppTemplates::LABELS as $key => $label)
            <a href="{{ $templates[$key] }}" target="_blank" rel="noopener" wire:click="markTemplate('{{ $key }}')" @class(['is-next' => $next === $key])>
              <span class="adm-wa__icon">{!! $waIcon !!}</span>
              <span>{{ $label }}</span>
              @if(in_array($key, $sentTemplates, true))<span class="adm-wa__sent">✓ DIHANTAR</span>@elseif($next === $key)<span class="adm-wa__sent" style="color:#1FAF54">SETERUSNYA</span>@endif
            </a>
          @endforeach
        </div>
      </section>

      <section class="adm-card">
        <h2 class="adm-card__title" style="margin-bottom:12px">Pelanggan</h2>
        <dl class="adm-kv">
          <div><dt>Telefon</dt><dd><a href="tel:{{ $lead->phone }}" style="color:inherit">{{ $lead->phone }}</a></dd></div>
          <div><dt>E-mel</dt><dd>{{ $lead->email ?: '-' }}</dd></div>
          <div><dt>Kategori</dt><dd>{{ $lead->customerCategoryLabel('ms') ?: ($lead->isGeneral() ? '-' : 'Outstation') }}</dd></div>
          @if($lead->company_name)<div><dt>Syarikat</dt><dd>{{ $lead->company_name }}</dd></div>@endif
          <div><dt>Pulang</dt><dd>{{ optional($lead->end_date)->format('d/m/Y') ?: '-' }}</dd></div>
          <div><dt>Penumpang</dt><dd>{{ $lead->passengers ?: '-' }}</dd></div>
          <div><dt>Tujuan</dt><dd>{{ $lead->purpose ?: '-' }}</dd></div>
          @if($lead->notes)<div><dt>Catatan</dt><dd>{{ $lead->notes }}</dd></div>@endif
          <div><dt>Sumber</dt><dd>{{ $lead->sourceLabel() }}{{ $lead->utm_source ? ' · '.$lead->utm_source : '' }}</dd></div>
        </dl>
      </section>

      <section class="adm-card">
        <h2 class="adm-card__title" style="margin-bottom:12px">Log aktiviti</h2>
        @if($lead->events->isEmpty())
          <p class="adm-hint">Tiada aktiviti direkod.</p>
        @else
          <ol class="adm-timeline">
            @foreach($lead->events->take(40) as $e)
              <li><time>{{ $e->created_at->format('d/m/Y H:i') }}{{ $e->user ? ' · '.$e->user->name : '' }}</time>{{ $e->message }}</li>
            @endforeach
          </ol>
        @endif
      </section>

      <section class="adm-card">
        <h2 class="adm-card__title" style="margin-bottom:10px">Tindakan lain</h2>
        @if(\App\Support\BookingSop::isClosed($lead->stage))
          <button type="button" wire:click="reopenBooking" class="adm-btn">Buka semula tempahan</button>
        @else
          <label class="adm-field" style="margin-bottom:10px"><span class="adm-label">Sebab (pilihan)</span><input type="text" wire:model="decisionNote" class="adm-input adm-input--sm"></label>
          <button type="button" wire:click="cancelBooking" wire:confirm="Batalkan tempahan {{ $lead->referenceNumber() }}?" class="adm-btn adm-btn--danger">Batalkan tempahan</button>
        @endif
      </section>
    </aside>
  </div>
</div>
