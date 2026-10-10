<div class="adm-grid" style="gap:20px;max-width:760px">
  <div class="adm-head">
    <div>
      <h1>Tetapan Pixel &amp; Tracking</h1>
      <p>Masukkan ID untuk setiap platform pengiklanan. ID ini digunakan pada semua halaman awam.</p>
    </div>
  </div>
  <form wire:submit="save" class="adm-card adm-form">
    <label class="adm-field"><span class="adm-label">Facebook / Meta Pixel ID</span>
      <input type="text" wire:model="meta" placeholder="Contoh: 1234567890123456" class="adm-input">
    </label>
    <label class="adm-field"><span class="adm-label">Google Ads Conversion ID</span>
      <input type="text" wire:model="google" placeholder="Contoh: AW-123456789" class="adm-input">
    </label>
    <label class="adm-field"><span class="adm-label">TikTok Pixel ID</span>
      <input type="text" wire:model="tiktok" placeholder="Contoh: CXXXXXXXXXXXXXX" class="adm-input">
    </label>
    <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap">
      <button type="submit" class="adm-btn adm-btn--primary">Simpan</button>
      @if($saved)<span class="adm-alert adm-alert--success" style="padding:8px 12px">Disimpan.</span>@endif
    </div>
  </form>
</div>
