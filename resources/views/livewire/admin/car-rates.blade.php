@php $rm = fn ($v) => \App\Support\Pricing::rm($v); @endphp
<div class="adm-grid" style="gap:20px">
  <div class="adm-head">
    <div>
      <h1>Harga Kereta</h1>
      <p>Harga retail dipaparkan kepada pelanggan. Harga ejen & VIP untuk rujukan dalaman sahaja. Pakej dikira automatik — ubah formula di <a href="{{ route('admin.payment-settings') }}" style="color:var(--accent-ink);font-weight:700">Harga &amp; Bayaran</a>.</p>
    </div>
    <div class="adm-head__actions"><button type="button" wire:click="create" class="adm-btn adm-btn--primary">+ Tambah kereta</button></div>
  </div>

  @if($flash)
    <div class="adm-alert adm-alert--success" role="status"><span>{{ $flash }}</span><button type="button" wire:click="$set('flash', null)" aria-label="Tutup">✕</button></div>
  @endif

  @if($showForm)
    <form wire:submit="save" class="adm-card adm-form">
      <h2 class="adm-card__title" style="margin-bottom:14px">{{ $editingId ? 'Kemas kini kereta' : 'Tambah kereta' }}</h2>
      <div class="adm-fgrid adm-fgrid--3">
        <label class="adm-field"><span class="adm-label">Jenama <em>*</em></span>
          <input type="text" wire:model="brand" class="adm-input" list="brand-list">
          <datalist id="brand-list">@foreach($brands as $b)<option value="{{ $b }}">@endforeach</datalist>
          @error('brand') <span class="adm-error">{{ $message }}</span> @enderror
        </label>
        <label class="adm-field"><span class="adm-label">Model <em>*</em></span>
          <input type="text" wire:model="model" class="adm-input">
          @error('model') <span class="adm-error">{{ $message }}</span> @enderror
        </label>
        <label class="adm-field"><span class="adm-label">Kategori <em>*</em></span>
          <select wire:model="category" class="adm-select">@foreach($categories as $k => [$label, $dep])<option value="{{ $k }}">{{ $label }} (deposit {{ $rm(\App\Support\Pricing::settings()['deposits'][$k] ?? $dep) }})</option>@endforeach</select>
        </label>
        <label class="adm-field"><span class="adm-label">Harga ejen (RM/hari)</span><input type="number" min="0" wire:model="agentRate" class="adm-input">@error('agentRate') <span class="adm-error">{{ $message }}</span> @enderror</label>
        <label class="adm-field"><span class="adm-label">Harga VIP (RM/hari)</span><input type="number" min="0" wire:model="vipRate" class="adm-input">@error('vipRate') <span class="adm-error">{{ $message }}</span> @enderror</label>
        <span></span>
        <label class="adm-field"><span class="adm-label">Retail minimum = harga booking <em>*</em></span><input type="number" min="1" wire:model="retailMin" class="adm-input">@error('retailMin') <span class="adm-error">{{ $message }}</span> @enderror</label>
        <label class="adm-field"><span class="adm-label">Retail maksimum</span><input type="number" min="1" wire:model="retailMax" class="adm-input" placeholder="Sama dengan minimum">@error('retailMax') <span class="adm-error">{{ $message }}</span> @enderror</label>
        <label class="adm-field"><span class="adm-label">Harga asal (kosong = auto)</span><input type="number" min="1" wire:model="listPrice" class="adm-input" placeholder="Auto: julat atas / +{{ \App\Support\Pricing::setting('list_markup') }}%">@error('listPrice') <span class="adm-error">{{ $message }}</span> @enderror</label>
        <label class="adm-field"><span class="adm-label">Deposit khas (kosong = ikut kategori)</span><input type="number" min="0" wire:model="deposit" class="adm-input">@error('deposit') <span class="adm-error">{{ $message }}</span> @enderror</label>
        <label class="adm-field" style="grid-column:span 2"><span class="adm-label">Catatan</span><input type="text" wire:model="notes" class="adm-input" placeholder="Contoh: 7 tempat duduk, bodykit GT4">@error('notes') <span class="adm-error">{{ $message }}</span> @enderror</label>
      </div>
      <div class="adm-btnrow" style="margin-top:16px">
        <label class="adm-check"><input type="checkbox" wire:model="isActive"> Aktif (dipaparkan dalam borang tempahan)</label>
        <span style="flex:1"></span>
        <button type="button" wire:click="cancel" class="adm-btn">Batal</button>
        <button type="submit" class="adm-btn adm-btn--primary">Simpan</button>
      </div>
    </form>
  @endif

  <section class="adm-card adm-card--flush">
    <div class="adm-toolbar" style="padding:16px">
      <label class="adm-search">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari jenama atau model..." class="adm-input" aria-label="Cari kereta">
      </label>
      <select wire:model.live="categoryFilter" class="adm-select" style="width:auto;min-width:180px" aria-label="Kategori">
        <option value="">Semua kategori</option>
        @foreach($categories as $k => [$label]) <option value="{{ $k }}">{{ $label }}</option> @endforeach
      </select>
      <span class="adm-hint">{{ $rates->count() }} kereta</span>
    </div>
    <div class="adm-table-wrap">
      <table class="adm-table adm-rates">
        <thead>
          <tr>
            <th>Kereta</th>
            <th>Kategori</th>
            <th class="num">Ejen <span class="adm-badge-int">DALAMAN</span></th>
            <th class="num">VIP</th>
            <th class="num">Harga asal</th>
            <th class="num">Booking/hari</th>
            <th class="num">3 hari</th>
            <th class="num">Mingguan</th>
            <th class="num">Bulanan</th>
            <th class="num">Tahunan</th>
            <th class="num">Deposit</th>
            <th style="text-align:right">Tindakan</th>
          </tr>
        </thead>
        <tbody>
          @forelse($rates as $r)
            @php $p = $r->packages(); @endphp
            <tr wire:key="rate-{{ $r->id }}" @class(['is-off' => ! $r->is_active])>
              <td><strong>{{ $r->brand }}</strong> {{ $r->model }}@if($r->notes)<span class="adm-sub">{{ $r->notes }}</span>@endif</td>
              <td>{{ $r->categoryLabel() }}</td>
              <td class="num">{{ $r->agent_rate ? $rm($r->agent_rate) : '-' }}</td>
              <td class="num">{{ $r->vip_rate ? $rm($r->vip_rate) : '-' }}</td>
              <td class="num" style="color:var(--muted);text-decoration:line-through">{{ $rm($r->listDaily()) }}</td>
              <td class="num"><strong>{{ $rm($r->bookingDaily()) }}</strong></td>
              <td class="num">{{ $rm($p['three_day']) }}</td>
              <td class="num">{{ $rm($p['weekly']) }}</td>
              <td class="num">{{ $rm($p['monthly']) }}</td>
              <td class="num">{{ $rm($p['yearly']) }}</td>
              <td class="num">{{ $rm($r->depositAmount()) }}</td>
              <td>
                <div class="adm-actions">
                  <button type="button" wire:click="edit({{ $r->id }})" class="adm-btn adm-btn--sm">Edit</button>
                  <button type="button" wire:click="toggleActive({{ $r->id }})" class="adm-btn adm-btn--sm adm-btn--soft">{{ $r->is_active ? 'Nyahaktif' : 'Aktifkan' }}</button>
                  <button type="button" wire:click="delete({{ $r->id }})" wire:confirm="Padam {{ $r->name() }} daripada senarai harga?" class="adm-btn adm-btn--sm adm-btn--danger">Padam</button>
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="12" class="adm-empty">Tiada kereta dijumpai.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </section>
</div>
