<div class="adm-grid" style="gap:20px;max-width:760px">
  <div class="adm-head">
    <div>
      <h1>Tetapan Laman</h1>
      <p>Nombor WhatsApp PIC dan e-mel notifikasi yang digunakan oleh semua halaman dan borang.</p>
    </div>
  </div>

  <form wire:submit="save" class="adm-card adm-form">
    <label class="adm-field"><span class="adm-label">Nombor WhatsApp PIC <em>*</em></span>
      <input type="text" wire:model="whatsappNumber" placeholder="60123456789" class="adm-input" inputmode="tel">
      <span class="adm-hint">Format antarabangsa tanpa + atau sengkang, contoh 60123456789.</span>
      @error('whatsappNumber') <span class="adm-error">{{ $message }}</span> @enderror
    </label>
    <label class="adm-field"><span class="adm-label">E-mel notifikasi admin <em>*</em></span>
      <input type="email" wire:model="adminEmail" placeholder="sewolah.hq@gmail.com" class="adm-input">
      <span class="adm-hint">Setiap permohonan baharu dihantar ke e-mel ini.</span>
      @error('adminEmail') <span class="adm-error">{{ $message }}</span> @enderror
    </label>
    <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap">
      <button type="submit" class="adm-btn adm-btn--primary">Simpan</button>
      @if($saved)<span class="adm-alert adm-alert--success" style="padding:8px 12px">Disimpan &amp; digunakan pada semua halaman dan borang.</span>@endif
    </div>
  </form>

  <section class="adm-card">
    <h2 class="adm-card__title" style="margin-bottom:12px">Pautan pantas</h2>
    <div style="display:flex;flex-wrap:wrap;gap:10px">
      <a href="{{ route('home') }}" target="_blank" rel="noopener" class="adm-btn">Laman utama ↗</a>
      <a href="{{ route('booking.form') }}" target="_blank" rel="noopener" class="adm-btn">Borang /form ↗</a>
      <a href="{{ route('landing') }}" target="_blank" rel="noopener" class="adm-btn">Outstation ↗</a>
    </div>
  </section>
</div>
