@php $rm = fn ($v) => \App\Support\Pricing::rm($v); @endphp
<div class="adm-grid" style="gap:20px;max-width:980px">
  <div class="adm-head">
    <div>
      <h1>Harga &amp; Bayaran</h1>
      <p>Akaun bank & DuitNow QR yang dipaparkan kepada pelanggan, nama PIC dalam template WhatsApp, dan formula pakej harga.</p>
    </div>
  </div>

  <form wire:submit="save" class="adm-grid" style="gap:20px">
    <section class="adm-card adm-form">
      <h2 class="adm-card__title" style="margin-bottom:14px">Bayaran (pindahan bank / DuitNow)</h2>
      <div class="adm-fgrid adm-fgrid--2">
        <label class="adm-field"><span class="adm-label">Nama bank</span><input type="text" wire:model="bankName" class="adm-input" placeholder="Contoh: Maybank">@error('bankName') <span class="adm-error">{{ $message }}</span> @enderror</label>
        <label class="adm-field"><span class="adm-label">No. akaun</span><input type="text" wire:model="bankAccountNumber" class="adm-input" inputmode="numeric">@error('bankAccountNumber') <span class="adm-error">{{ $message }}</span> @enderror</label>
        <label class="adm-field"><span class="adm-label">Nama pemegang akaun</span><input type="text" wire:model="bankAccountName" class="adm-input" placeholder="Contoh: DBENA SDN BHD">@error('bankAccountName') <span class="adm-error">{{ $message }}</span> @enderror</label>
        <label class="adm-field"><span class="adm-label">Nama PIC (template WhatsApp)</span><input type="text" wire:model="picName" class="adm-input" placeholder="Contoh: Nizam — SEWOLAH">@error('picName') <span class="adm-error">{{ $message }}</span> @enderror</label>
      </div>
      <div class="adm-fgrid adm-fgrid--2" style="margin-top:14px;align-items:start">
        <label class="adm-field"><span class="adm-label">Kod QR DuitNow (gambar)</span>
          <input type="file" wire:model="qr" accept="image/*" class="adm-input">
          <span class="adm-hint" wire:loading wire:target="qr">Memuat naik…</span>
          @error('qr') <span class="adm-error">{{ $message }}</span> @enderror
        </label>
        <div>
          @if($qr)
            <span class="adm-hint">QR baharu dipilih — tekan Simpan.</span>
          @elseif($settings->qrUrl())
            <img src="{{ $settings->qrUrl() }}" alt="DuitNow QR" style="width:140px;height:auto;border-radius:10px;border:1px solid var(--border);background:#fff;padding:6px">
            <div><button type="button" wire:click="removeQr" wire:confirm="Buang kod QR?" class="adm-btn adm-btn--sm adm-btn--danger" style="margin-top:8px">Buang QR</button></div>
          @else
            <span class="adm-hint">Tiada QR. Pelanggan hanya akan nampak butiran akaun bank.</span>
          @endif
        </div>
      </div>
      @unless($settings->hasBankDetails())
        <div class="adm-callout" style="margin-top:14px"><strong>Penting:</strong> isi nama bank & nombor akaun supaya pelanggan boleh terus membuat bayaran di portal tempahan.</div>
      @endunless
    </section>

    <section class="adm-card adm-form">
      <h2 class="adm-card__title" style="margin-bottom:4px">Formula harga pakej</h2>
      <p class="adm-card__sub" style="margin-bottom:14px">Contoh harga booking RM150/hari: 3 hari {{ $rm($example['three_day']) }} · mingguan {{ $rm($example['weekly']) }} · bulanan {{ $rm($example['monthly']) }} · tahunan {{ $rm($example['yearly']) }} (disimpan semula selepas Simpan).</p>
      <div class="adm-fgrid adm-fgrid--3">
        @foreach(\App\Livewire\Admin\PaymentSettings::NUMERIC as $key => [$label, $hint])
          <label class="adm-field"><span class="adm-label">{{ $label }}</span>
            <input type="number" step="any" min="0" wire:model="pricing.{{ $key }}" class="adm-input">
            <span class="adm-hint">{{ $hint }}</span>
            @error('pricing.'.$key) <span class="adm-error">{{ $message }}</span> @enderror
          </label>
        @endforeach
      </div>
    </section>

    <section class="adm-card adm-form">
      <h2 class="adm-card__title" style="margin-bottom:4px">Deposit keselamatan ikut kategori (RM)</h2>
      <p class="adm-card__sub" style="margin-bottom:14px">Sewaan 30 hari ke atas: deposit = 1 bulan sewa (NOTA 129). Deposit khas setiap kereta boleh ditetapkan di Harga Kereta.</p>
      <div class="adm-fgrid adm-fgrid--3">
        @foreach($categories as $key => [$label])
          <label class="adm-field"><span class="adm-label">{{ $label }}</span>
            <input type="number" min="0" wire:model="deposits.{{ $key }}" class="adm-input">
            @error('deposits.'.$key) <span class="adm-error">{{ $message }}</span> @enderror
          </label>
        @endforeach
      </div>
    </section>

    <div class="adm-btnrow">
      <button type="submit" class="adm-btn adm-btn--primary">Simpan tetapan</button>
      @if($saved)<span class="adm-alert adm-alert--success" style="padding:8px 12px">Disimpan.</span>@endif
    </div>
  </form>
</div>
