<div class="adm-grid" style="gap:20px">
  <div class="adm-head">
    <div>
      <h1>Pengurusan Kenderaan</h1>
      <p>Kenderaan yang dipaparkan di laman utama, borang permohonan dan halaman outstation.</p>
    </div>
  </div>

  <div class="adm-grid adm-grid--main" style="align-items:start">
    <div class="adm-vgrid">
      @forelse($vehicles as $v)
        <article class="adm-card adm-vcard" wire:key="vehicle-{{ $v->id }}">
          <div class="adm-vcard__img">
            <img src="{{ $v->imageUrl() }}" alt="{{ $v->name }}" loading="lazy">
          </div>
          <div class="adm-vcard__body">
            <div class="adm-vcard__flags">
              <span class="adm-pill {{ $v->is_active ? 'adm-pill--disahkan' : 'adm-pill--batal' }}">{{ $v->is_active ? 'Aktif' : 'Tidak aktif' }}</span>
              @if($v->is_featured)<span class="adm-tag adm-tag--general">FEATURED</span>@endif
            </div>
            <h3 style="font-size:16px;font-weight:800">{{ $v->name }}</h3>
            <p class="adm-hint">{{ $v->category }}</p>
            <div style="display:flex;gap:8px;margin-top:6px">
              <button type="button" wire:click="edit({{ $v->id }})" class="adm-btn adm-btn--sm adm-btn--soft">Edit</button>
              <button type="button" wire:click="delete({{ $v->id }})" wire:confirm="Padam kenderaan {{ $v->name }}?" class="adm-btn adm-btn--sm adm-btn--danger">Padam</button>
            </div>
          </div>
        </article>
      @empty
        <p class="adm-empty">Tiada kenderaan lagi.</p>
      @endforelse
    </div>

    <form wire:submit="save" class="adm-card adm-form">
      <div>
        <h2 class="adm-card__title">{{ $editingId ? 'Edit kenderaan' : 'Tambah kenderaan baharu' }}</h2>
        <p class="adm-card__sub">Gambar disyorkan nisbah 4:3, sekurang-kurangnya 1200px lebar.</p>
      </div>
      <label class="adm-field"><span class="adm-label">Nama kenderaan <em>*</em></span>
        <input type="text" wire:model="name" class="adm-input" placeholder="Contoh: Toyota Alphard SC">
        @error('name') <span class="adm-error">{{ $message }}</span> @enderror
      </label>
      <label class="adm-field"><span class="adm-label">Kategori <em>*</em></span>
        <input type="text" wire:model="category" class="adm-input" placeholder="Contoh: PREMIUM FAMILY MPV">
        @error('category') <span class="adm-error">{{ $message }}</span> @enderror
      </label>
      <label class="adm-field"><span class="adm-label">Tags (BM) — pisah dengan koma</span>
        <input type="text" wire:model="tagsMs" class="adm-input">
      </label>
      <label class="adm-field"><span class="adm-label">Tags (EN) — comma separated</span>
        <input type="text" wire:model="tagsEn" class="adm-input">
      </label>
      <label class="adm-field"><span class="adm-label">Label butang (CTA)</span>
        <input type="text" wire:model="ctaLabel" class="adm-input" placeholder="Contoh: TEMPAH ALPHARD">
      </label>
      <label class="adm-field"><span class="adm-label">Gambar kenderaan</span>
        <input type="file" wire:model="newImage" accept="image/*" class="adm-input" style="padding:8px">
        <span wire:loading wire:target="newImage" class="adm-hint">Memuat naik...</span>
      </label>
      <div style="display:flex;gap:20px">
        <label class="adm-check"><input type="checkbox" wire:model="isFeatured"> Featured</label>
        <label class="adm-check"><input type="checkbox" wire:model="isActive"> Aktif</label>
      </div>
      <div style="display:flex;gap:10px">
        <button type="submit" class="adm-btn adm-btn--primary">Simpan</button>
        @if($editingId)
          <button type="button" wire:click="resetForm" class="adm-btn">Batal</button>
        @endif
      </div>
    </form>
  </div>
</div>
