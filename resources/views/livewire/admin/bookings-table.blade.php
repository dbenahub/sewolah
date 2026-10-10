@php
  $statusLabels = \App\Livewire\Admin\Dashboard::STATUS_LABELS;
  $states = trans('form.states', [], 'ms');
  $catOptions = collect(\App\Models\Lead::CUSTOMER_CATEGORIES)->mapWithKeys(fn ($c) => [$c => trans('form.categories.'.$c.'.title', [], 'ms')])->all();
  $licOptions = ['malaysia' => trans('form.license_options.malaysia', [], 'ms'), 'international' => trans('form.license_options.international', [], 'ms')];
@endphp
<div class="adm-grid" style="gap:20px">
  <div class="adm-head">
    <div>
      <h1>Tempahan &amp; Pelanggan</h1>
      <p>Urus semua permohonan daripada borang utama dan halaman outstation.</p>
    </div>
    <div class="adm-head__actions">
      <button type="button" wire:click="exportCsv" class="adm-btn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 4v11m0 0-4-4m4 4 4-4M5 19h14"/></svg>
        Export CSV
      </button>
    </div>
  </div>

  @if($flash)
    <div class="adm-alert adm-alert--success" role="status">
      <span>{{ $flash }}</span>
      <button type="button" wire:click="dismissFlash" aria-label="Tutup">✕</button>
    </div>
  @endif

  <section class="adm-card adm-card--flush">
    <div class="adm-toolbar" style="padding:16px">
      <label class="adm-search">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
        <input type="search" wire:model.live.debounce.400ms="search" placeholder="Cari nama, telefon atau e-mel..." class="adm-input" aria-label="Cari">
      </label>
      <select wire:model.live="statusFilter" class="adm-select" style="width:auto;min-width:170px" aria-label="Status">
        <option value="">Semua status</option>
        @foreach($statusLabels as $key => $label)
          <option value="{{ $key }}">{{ $label }}</option>
        @endforeach
      </select>
      <select wire:model.live="sourceFilter" class="adm-select" style="width:auto;min-width:170px" aria-label="Sumber">
        <option value="">Semua sumber</option>
        <option value="general">Borang utama (/form)</option>
        <option value="outstation">Outstation</option>
      </select>
    </div>

    @if($leads->isEmpty())
      <p class="adm-empty">Tiada tempahan dijumpai.</p>
    @else
      <div class="adm-table-wrap">
        <table class="adm-table">
          <thead>
            <tr>
              <th>Rujukan</th>
              <th>Pelanggan</th>
              <th>Kenderaan</th>
              <th>Sumber</th>
              <th>Tarikh ambil</th>
              <th>Status</th>
              <th style="text-align:right">Tindakan</th>
            </tr>
          </thead>
          <tbody>
            @foreach($leads as $lead)
              <tr wire:key="lead-row-{{ $lead->id }}">
                <td class="adm-ref">{{ $lead->referenceNumber() }}</td>
                <td>
                  <button type="button" wire:click="viewLead({{ $lead->id }})" class="adm-name">{{ $lead->full_name }}</button>
                  <span class="adm-sub">{{ $lead->phone }}</span>
                </td>
                <td>{{ $lead->vehicle_name_snapshot ?: '-' }}</td>
                <td>
                  <span class="adm-tag adm-tag--{{ $lead->isGeneral() ? 'general' : 'outstation' }}">{{ $lead->isGeneral() ? 'UTAMA' : 'OUTSTATION' }}</span>
                  @if($lead->customer_category)<span class="adm-sub">{{ $lead->customerCategoryLabel('ms') }}</span>@endif
                </td>
                <td class="num">{{ optional($lead->arrival_date)->format('d/m/Y') ?: '-' }}</td>
                <td>
                  <select wire:change="updateStatus({{ $lead->id }}, $event.target.value)" class="adm-select adm-status-select" aria-label="Status {{ $lead->referenceNumber() }}">
                    @foreach($statusLabels as $key => $label)
                      <option value="{{ $key }}" @selected($lead->status === $key)>{{ $label }}</option>
                    @endforeach
                  </select>
                </td>
                <td>
                  <div class="adm-actions">
                    <button type="button" wire:click="viewLead({{ $lead->id }})" class="adm-btn adm-btn--sm">Lihat</button>
                    <button type="button" wire:click="editLead({{ $lead->id }})" class="adm-btn adm-btn--sm adm-btn--soft">Edit</button>
                    <button type="button" wire:click="deleteLead({{ $lead->id }})" wire:confirm="Padam tempahan {{ $lead->referenceNumber() }} ({{ $lead->full_name }})? Tindakan ini tidak boleh dibatalkan." class="adm-btn adm-btn--sm adm-btn--danger">Padam</button>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @if($leads->hasPages())
        <div class="adm-pagination" style="display:flex;align-items:center;justify-content:space-between;gap:12px">
          <span class="adm-hint">Halaman {{ $leads->currentPage() }} dari {{ $leads->lastPage() }} · {{ $leads->total() }} tempahan</span>
          <div style="display:flex;gap:8px">
            <button type="button" class="adm-btn adm-btn--sm" wire:click="previousPage" @disabled($leads->onFirstPage())>← Sebelum</button>
            <button type="button" class="adm-btn adm-btn--sm" wire:click="nextPage" @disabled(! $leads->hasMorePages())>Seterusnya →</button>
          </div>
        </div>
      @endif
    @endif
  </section>

  @if($selectedLead)
    @php
      $isGen = $selectedLead->isGeneral();
      $waCustomer = preg_replace('/[^0-9]/', '', (string) $selectedLead->phone);
      if (str_starts_with($waCustomer, '0')) { $waCustomer = '6'.$waCustomer; }
      $selTime = $selectedLead->arrival_time ? substr((string) $selectedLead->arrival_time, 0, 5) : '';
      $detailRows = $isGen ? [
        'Kategori' => $selectedLead->customerCategoryLabel('ms'),
        'Telefon' => $selectedLead->phone, 'E-mel' => $selectedLead->email,
        'Syarikat' => $selectedLead->company_name,
        'Lesen' => $selectedLead->driver_license ? ($licOptions[$selectedLead->driver_license] ?? $selectedLead->driver_license) : null,
        'Tujuan' => $selectedLead->purpose, 'Negeri ambil' => $selectedLead->pickup_state,
        'Lokasi ambil' => $selectedLead->pickup_location, 'Lokasi pulang' => $selectedLead->return_location ?: 'Sama seperti lokasi ambil',
        'Tarikh / masa ambil' => trim(optional($selectedLead->arrival_date)->format('d/m/Y').' '.$selTime),
        'Tarikh pulang' => optional($selectedLead->end_date)->format('d/m/Y'),
        'Kenderaan' => $selectedLead->vehicle_name_snapshot, 'Penumpang' => $selectedLead->passengers,
        'Dihantar' => optional($selectedLead->submitted_at)->format('d/m/Y H:i'),
      ] : [
        'Telefon' => $selectedLead->phone, 'E-mel' => $selectedLead->email,
        'Datang dari' => $selectedLead->origin, 'Airport' => $selectedLead->airport,
        'Tarikh / masa ketibaan' => trim(optional($selectedLead->arrival_date)->format('d/m/Y').' '.$selTime),
        'Tarikh tamat sewa' => optional($selectedLead->end_date)->format('d/m/Y'),
        'Tujuan' => $selectedLead->purpose, 'Kenderaan' => $selectedLead->vehicle_name_snapshot,
        'Model lain' => $selectedLead->other_vehicle_model,
        'Penumpang' => $selectedLead->passengers, 'Luggage' => $selectedLead->luggage,
        'Destinasi' => $selectedLead->destination,
        'Dihantar' => optional($selectedLead->submitted_at)->format('d/m/Y H:i'),
      ];
      // [key, label, type, options|null, required]
      $customerFields = [
        ['full_name', 'Nama penuh', 'text', null, true],
        ['phone', 'Telefon', 'text', null, true],
        ['email', 'E-mel', 'email', null, false],
        ['status', 'Status', 'select', $statusLabels, true],
      ];
      if ($isGen) {
        $customerFields[] = ['customer_category', 'Kategori', 'select', ['' => '-'] + $catOptions, false];
        $customerFields[] = ['company_name', 'Syarikat', 'text', null, false];
        $customerFields[] = ['driver_license', 'Lesen', 'select', ['' => '-'] + $licOptions, false];
      }
      $rentalFields = [['purpose', 'Tujuan', 'text', null, false]];
      if ($isGen) {
        $rentalFields[] = ['pickup_state', 'Negeri ambil', 'select', ['' => '-'] + array_combine($states, $states), false];
        $rentalFields[] = ['pickup_location', 'Lokasi ambil', 'text', null, false];
        $rentalFields[] = ['return_location', 'Lokasi pulang', 'text', null, false];
      } else {
        $rentalFields[] = ['origin', 'Datang dari', 'text', null, false];
        $rentalFields[] = ['airport', 'Airport', 'text', null, false];
        $rentalFields[] = ['destination', 'Destinasi', 'text', null, false];
      }
      $rentalFields[] = ['arrival_date', $isGen ? 'Tarikh ambil' : 'Tarikh ketibaan', 'date', null, false];
      $rentalFields[] = ['arrival_time', $isGen ? 'Masa ambil' : 'Masa ketibaan', 'time', null, false];
      $rentalFields[] = ['end_date', $isGen ? 'Tarikh pulang' : 'Tarikh tamat sewa', 'date', null, false];
      $rentalFields[] = ['vehicle_name_snapshot', 'Kenderaan', 'text', null, false];
      if (! $isGen) {
        $rentalFields[] = ['other_vehicle_model', 'Model lain', 'text', null, false];
        $rentalFields[] = ['luggage', 'Luggage', 'text', null, false];
      }
      $rentalFields[] = ['passengers', 'Penumpang', 'number', null, false];
    @endphp

    <div class="adm-modal" role="dialog" aria-modal="true" aria-labelledby="lead-modal-title"
         x-data x-on:keydown.escape.window="$wire.closeLead()" wire:key="lead-modal-{{ $selectedLead->id }}-{{ $mode }}">
      <div class="adm-modal__backdrop" wire:click="closeLead"></div>
      <div class="adm-modal__panel">
        <div class="adm-modal__head">
          <div>
            <p class="adm-modal__eyebrow">{{ $selectedLead->referenceNumber() }} · {{ $isGen ? 'BORANG UTAMA' : 'OUTSTATION' }}</p>
            <h2 id="lead-modal-title" class="adm-modal__title">{{ $mode === 'edit' ? 'Edit tempahan' : $selectedLead->full_name }}</h2>
            @if($mode === 'view')
              <p style="margin-top:6px"><span class="adm-pill adm-pill--{{ $selectedLead->status }}">{{ $statusLabels[$selectedLead->status] ?? $selectedLead->status }}</span></p>
            @endif
          </div>
          <button type="button" wire:click="closeLead" class="adm-iconbtn" aria-label="Tutup">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18"/></svg>
          </button>
        </div>

        @if($mode === 'view')
          <div class="adm-modal__body">
            <dl class="adm-dl">
              @foreach($detailRows as $label => $value)
                <div><dt>{{ $label }}</dt><dd>{{ ($value === null || $value === '') ? '-' : $value }}</dd></div>
              @endforeach
            </dl>
            <div>
              <p class="adm-section-label" style="margin-bottom:8px">Catatan</p>
              <div class="adm-note">{{ $selectedLead->notes ?: '-' }}</div>
            </div>
            @if($selectedLead->utm_source || $selectedLead->utm_campaign || $selectedLead->fbclid)
              <p class="adm-hint">Sumber iklan: {{ $selectedLead->utm_source ?: '-' }} / {{ $selectedLead->utm_campaign ?: '-' }}{{ $selectedLead->fbclid ? ' · Facebook click' : '' }}</p>
            @endif
          </div>
          <div class="adm-modal__foot">
            <a href="https://wa.me/{{ $waCustomer }}" target="_blank" rel="noopener" class="adm-btn adm-btn--wa">WhatsApp pelanggan</a>
            <div class="adm-modal__foot-right">
              <button type="button" wire:click="deleteLead({{ $selectedLead->id }})" wire:confirm="Padam tempahan {{ $selectedLead->referenceNumber() }} ({{ $selectedLead->full_name }})? Tindakan ini tidak boleh dibatalkan." class="adm-btn adm-btn--danger">Padam</button>
              <button type="button" wire:click="editLead({{ $selectedLead->id }})" class="adm-btn adm-btn--primary">Edit</button>
            </div>
          </div>
        @else
          <form wire:submit="saveLead" style="display:contents">
            <div class="adm-modal__body">
              <p class="adm-section-label">Maklumat pelanggan</p>
              <div class="adm-fgrid adm-fgrid--3">
                @foreach($customerFields as [$key, $label, $type, $options, $required])
                  <label class="adm-field">
                    <span class="adm-label">{{ $label }} @if($required)<em>*</em>@endif</span>
                    @if($type === 'select')
                      <select wire:model="form.{{ $key }}" class="adm-select">
                        @foreach($options as $val => $text)<option value="{{ $val }}">{{ $text }}</option>@endforeach
                      </select>
                    @else
                      <input type="{{ $type }}" wire:model="form.{{ $key }}" class="adm-input">
                    @endif
                    @error('form.'.$key) <span class="adm-error">{{ $message }}</span> @enderror
                  </label>
                @endforeach
              </div>

              <p class="adm-section-label">Butiran sewaan</p>
              <div class="adm-fgrid adm-fgrid--3">
                @foreach($rentalFields as [$key, $label, $type, $options, $required])
                  <label class="adm-field">
                    <span class="adm-label">{{ $label }}</span>
                    @if($type === 'select')
                      <select wire:model="form.{{ $key }}" class="adm-select">
                        @foreach($options as $val => $text)<option value="{{ $val }}">{{ $text }}</option>@endforeach
                      </select>
                    @else
                      <input type="{{ $type }}" wire:model="form.{{ $key }}" class="adm-input" @if($type === 'number') min="1" max="50" @endif>
                    @endif
                    @error('form.'.$key) <span class="adm-error">{{ $message }}</span> @enderror
                  </label>
                @endforeach
              </div>

              <label class="adm-field">
                <span class="adm-label">Catatan</span>
                <textarea wire:model="form.notes" rows="3" class="adm-textarea"></textarea>
                @error('form.notes') <span class="adm-error">{{ $message }}</span> @enderror
              </label>
            </div>
            <div class="adm-modal__foot">
              <div class="adm-modal__foot-right">
                <button type="button" wire:click="cancelEdit" class="adm-btn">Batal</button>
                <button type="submit" class="adm-btn adm-btn--primary" wire:loading.attr="disabled" wire:target="saveLead">
                  <span wire:loading.remove wire:target="saveLead">Simpan perubahan</span>
                  <span wire:loading wire:target="saveLead">Menyimpan...</span>
                </button>
              </div>
            </div>
          </form>
        @endif
      </div>
    </div>
  @endif
</div>
