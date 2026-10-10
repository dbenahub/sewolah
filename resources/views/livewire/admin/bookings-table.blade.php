<div class="grid gap-6">
  <div class="flex flex-wrap gap-3 justify-between items-center">
    <h1 class="m-0 text-2xl font-extrabold">Tempahan &amp; Pelanggan</h1>
    <button wire:click="exportCsv" class="bg-white/10 text-white text-xs font-bold px-3.5 py-2 rounded-lg">Export CSV</button>
  </div>

  @if($flash)
    <div class="flex items-center justify-between gap-3 bg-green-500/10 border border-green-500/30 text-green-300 text-[13px] font-semibold px-4 py-3 rounded-xl">
      <span>{{ $flash }}</span>
      <button type="button" wire:click="dismissFlash" class="text-green-300/70 hover:text-green-200">✕</button>
    </div>
  @endif

  <div class="flex flex-wrap gap-3">
    <input type="text" wire:model.live.debounce.400ms="search" placeholder="Cari nama / telefon..." class="px-3.5 py-2.5 rounded-lg border border-white/15 bg-[#0A0A0A] text-white text-sm flex-1 min-w-[200px]">
    <select wire:model.live="statusFilter" class="px-3.5 py-2.5 rounded-lg border border-white/15 bg-[#0A0A0A] text-white text-sm">
      <option value="" class="bg-[#0A0A0A] text-white">Semua Status</option>
      @foreach(['baru','dihubungi','quotation_dihantar','disahkan','batal'] as $s)
        <option value="{{ $s }}" class="bg-[#0A0A0A] text-white">{{ ucfirst(str_replace('_',' ',$s)) }}</option>
      @endforeach
    </select>
    <select wire:model.live="sourceFilter" class="px-3.5 py-2.5 rounded-lg border border-white/15 bg-[#0A0A0A] text-white text-sm">
      <option value="" class="bg-[#0A0A0A] text-white">Semua Sumber</option>
      <option value="general" class="bg-[#0A0A0A] text-white">Borang Utama (/form)</option>
      <option value="outstation" class="bg-[#0A0A0A] text-white">Outstation</option>
    </select>
  </div>

  @if($leads->isEmpty())
    <p class="m-0 text-[13px] text-white/45">Tiada tempahan dijumpai.</p>
  @else
    <div class="overflow-x-auto bg-[#141414] border border-white/8 rounded-2xl">
      <table class="w-full border-collapse text-[13px]">
        <thead>
          <tr class="text-left text-white/50">
            <th class="p-3.5 font-bold">Rujukan</th>
            <th class="p-3.5 font-bold">Nama</th>
            <th class="p-3.5 font-bold">Telefon</th>
            <th class="p-3.5 font-bold">Kenderaan</th>
            <th class="p-3.5 font-bold">Sumber / Kategori</th>
            <th class="p-3.5 font-bold">Tarikh Ambil</th>
            <th class="p-3.5 font-bold">Status</th>
            <th class="p-3.5"></th>
          </tr>
        </thead>
        <tbody>
          @foreach($leads as $lead)
            <tr class="border-t border-white/6">
              <td class="p-3.5 whitespace-nowrap text-white/60">{{ $lead->referenceNumber() }}</td>
              <td class="p-3.5"><button type="button" wire:click="viewLead({{ $lead->id }})" class="text-left font-semibold hover:text-brand-red">{{ $lead->full_name }}</button></td>
              <td class="p-3.5">{{ $lead->phone }}</td>
              <td class="p-3.5">{{ $lead->vehicle_name_snapshot }}</td>
              <td class="p-3.5">
                <span class="inline-block text-[11px] font-bold px-2 py-0.5 rounded {{ $lead->isGeneral() ? 'bg-brand-red/20 text-red-300' : 'bg-white/10 text-white/70' }}">{{ $lead->isGeneral() ? 'UTAMA' : 'OUTSTATION' }}</span>
                @if($lead->customer_category)<span class="block mt-1 text-xs text-white/55">{{ $lead->customerCategoryLabel('ms') }}</span>@endif
              </td>
              <td class="p-3.5">{{ optional($lead->arrival_date)->format('d/m/Y') }}</td>
              <td class="p-3.5">
                <select wire:change="updateStatus({{ $lead->id }}, $event.target.value)" class="bg-[#0A0A0A] text-white border border-white/15 rounded px-2 py-1 text-xs">
                  @foreach(['baru','dihubungi','quotation_dihantar','disahkan','batal'] as $s)
                    <option value="{{ $s }}" class="bg-[#0A0A0A] text-white" @selected($lead->status === $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                  @endforeach
                </select>
              </td>
              <td class="p-3.5">
                <div class="flex gap-1.5 justify-end whitespace-nowrap">
                  <button type="button" wire:click="viewLead({{ $lead->id }})" class="bg-transparent border border-white/15 hover:border-white/40 text-white text-xs font-semibold px-3 py-1.5 rounded-lg">Lihat</button>
                  <button type="button" wire:click="editLead({{ $lead->id }})" class="bg-white/10 hover:bg-white/20 text-white text-xs font-semibold px-3 py-1.5 rounded-lg">Edit</button>
                  <button type="button" wire:click="deleteLead({{ $lead->id }})" wire:confirm="Padam tempahan {{ $lead->referenceNumber() }} ({{ $lead->full_name }})? Tindakan ini tidak boleh dibatalkan." class="bg-brand-red/15 hover:bg-brand-red/30 text-red-300 text-xs font-semibold px-3 py-1.5 rounded-lg">Padam</button>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div>{{ $leads->links() }}</div>
  @endif

  @if($selectedLead)
    @php
      $isGen = $selectedLead->isGeneral();
      $waCustomer = preg_replace('/[^0-9]/', '', (string) $selectedLead->phone);
      if (str_starts_with($waCustomer, '0')) { $waCustomer = '6'.$waCustomer; }
      $selTime = $selectedLead->arrival_time ? substr((string) $selectedLead->arrival_time, 0, 5) : '';
      $detailRows = $isGen ? [
        'No. Rujukan' => $selectedLead->referenceNumber(), 'Sumber' => $selectedLead->sourceLabel(),
        'Kategori' => $selectedLead->customerCategoryLabel('ms'), 'Status' => ucfirst(str_replace('_',' ',$selectedLead->status)),
        'Nama' => $selectedLead->full_name, 'Syarikat' => $selectedLead->company_name ?: '-',
        'Telefon' => $selectedLead->phone, 'Email' => $selectedLead->email ?: '-',
        'Lesen' => $selectedLead->driver_license ? trans('form.license_options.'.$selectedLead->driver_license, [], 'ms') : '-',
        'Tujuan' => $selectedLead->purpose, 'Negeri Ambil' => $selectedLead->pickup_state,
        'Lokasi Ambil' => $selectedLead->pickup_location, 'Lokasi Pulang' => $selectedLead->return_location ?: 'Sama seperti lokasi ambil',
        'Tarikh/Masa Ambil' => trim(optional($selectedLead->arrival_date)->format('d/m/Y').' '.$selTime),
        'Tarikh Pulang' => optional($selectedLead->end_date)->format('d/m/Y'),
        'Kenderaan' => $selectedLead->vehicle_name_snapshot, 'Penumpang' => $selectedLead->passengers,
        'Dihantar' => optional($selectedLead->submitted_at)->timezone(config('app.timezone'))->format('d/m/Y H:i'),
      ] : [
        'No. Rujukan' => $selectedLead->referenceNumber(), 'Sumber' => $selectedLead->sourceLabel(),
        'Status' => ucfirst(str_replace('_',' ',$selectedLead->status)),
        'Nama' => $selectedLead->full_name, 'Telefon' => $selectedLead->phone, 'Email' => $selectedLead->email ?: '-',
        'Datang Dari' => $selectedLead->origin, 'Airport' => $selectedLead->airport,
        'Tarikh/Masa Ketibaan' => trim(optional($selectedLead->arrival_date)->format('d/m/Y').' '.$selTime),
        'Tarikh Tamat Sewa' => optional($selectedLead->end_date)->format('d/m/Y'),
        'Tujuan' => $selectedLead->purpose, 'Kenderaan' => $selectedLead->vehicle_name_snapshot,
        'Model Lain' => $selectedLead->other_vehicle_model ?: '-',
        'Penumpang' => $selectedLead->passengers, 'Luggage' => $selectedLead->luggage,
        'Destinasi' => $selectedLead->destination,
        'Dihantar' => optional($selectedLead->submitted_at)->timezone(config('app.timezone'))->format('d/m/Y H:i'),
      ];
      $inp = 'w-full box-border px-3 py-2.5 rounded-lg border border-white/15 bg-[#0A0A0A] text-white text-[13.5px] focus:outline-none focus:border-white/50';
      $lbl = 'grid gap-1.5 text-[11.5px] font-bold tracking-wide text-white/55';
    @endphp

    <div class="fixed inset-0 z-[100] flex items-start md:items-center justify-center p-3 md:p-6"
         x-data x-on:keydown.escape.window="$wire.closeLead()" role="dialog" aria-modal="true" wire:key="lead-modal-{{ $selectedLead->id }}-{{ $mode }}">
      <div class="absolute inset-0 bg-black/75 backdrop-blur-sm" wire:click="closeLead"></div>

      <div class="relative w-full max-w-4xl max-h-[92vh] overflow-y-auto bg-[#141414] border border-white/10 rounded-2xl shadow-2xl">
        {{-- Header --}}
        <div class="sticky top-0 z-10 flex flex-wrap items-center justify-between gap-3 bg-[#141414] border-b border-white/8 px-5 md:px-7 py-4">
          <div>
            <p class="m-0 text-[11px] font-bold tracking-[0.18em] text-brand-red">{{ $selectedLead->referenceNumber() }} · {{ $isGen ? 'BORANG UTAMA' : 'OUTSTATION' }}</p>
            <p class="m-0 mt-0.5 text-lg font-extrabold">{{ $mode === 'edit' ? 'Edit Tempahan' : 'Detail Pelanggan' }} — {{ $selectedLead->full_name }}</p>
          </div>
          <button type="button" wire:click="closeLead" class="text-white/60 hover:text-white text-[13px] font-semibold border border-white/15 rounded-lg px-3 py-1.5">Tutup ✕</button>
        </div>

        @if($mode === 'view')
          <div class="px-5 md:px-7 py-6 grid gap-6">
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-4 text-[13.5px]">
              @foreach($detailRows as $label => $value)
                <div>
                  <p class="m-0 mb-0.5 text-[11.5px] font-bold tracking-wide text-white/45">{{ $label }}</p>
                  <p class="m-0 break-words">{{ $value === null || $value === '' ? '-' : $value }}</p>
                </div>
              @endforeach
            </div>
            <div>
              <p class="m-0 mb-1 text-[11.5px] font-bold tracking-wide text-white/45">Catatan</p>
              <p class="m-0 text-[13.5px] whitespace-pre-line bg-[#0A0A0A] border border-white/8 rounded-lg p-3.5">{{ $selectedLead->notes ?: '-' }}</p>
            </div>
            @if($selectedLead->utm_source || $selectedLead->utm_campaign || $selectedLead->fbclid)
              <p class="m-0 text-[12px] text-white/45">Sumber iklan: {{ $selectedLead->utm_source ?: '-' }} / {{ $selectedLead->utm_campaign ?: '-' }}{{ $selectedLead->fbclid ? ' · Facebook click' : '' }}</p>
            @endif
          </div>
          <div class="sticky bottom-0 flex flex-wrap gap-2.5 justify-between bg-[#141414] border-t border-white/8 px-5 md:px-7 py-4">
            <a href="https://wa.me/{{ $waCustomer }}" target="_blank" rel="noopener" class="bg-[#1FAF54] hover:bg-[#188f44] text-white text-[13px] font-bold px-4 py-2.5 rounded-lg">WhatsApp Pelanggan</a>
            <div class="flex gap-2.5">
              <button type="button" wire:click="deleteLead({{ $selectedLead->id }})" wire:confirm="Padam tempahan {{ $selectedLead->referenceNumber() }} ({{ $selectedLead->full_name }})? Tindakan ini tidak boleh dibatalkan." class="bg-brand-red/15 hover:bg-brand-red/30 text-red-300 text-[13px] font-bold px-4 py-2.5 rounded-lg">Padam</button>
              <button type="button" wire:click="editLead({{ $selectedLead->id }})" class="bg-white text-black hover:bg-white/90 text-[13px] font-bold px-5 py-2.5 rounded-lg">Edit</button>
            </div>
          </div>
        @else
          <form wire:submit="saveLead">
            <div class="px-5 md:px-7 py-6 grid gap-5">
              <p class="m-0 text-[11.5px] font-bold tracking-[0.18em] text-white/40">MAKLUMAT PELANGGAN</p>
              <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <label class="{{ $lbl }}">Nama Penuh *
                  <input type="text" wire:model="form.full_name" class="{{ $inp }}">
                  @error('form.full_name') <span class="text-red-400 text-xs font-semibold">{{ $message }}</span> @enderror
                </label>
                <label class="{{ $lbl }}">Telefon *
                  <input type="text" wire:model="form.phone" class="{{ $inp }}">
                  @error('form.phone') <span class="text-red-400 text-xs font-semibold">{{ $message }}</span> @enderror
                </label>
                <label class="{{ $lbl }}">Email
                  <input type="email" wire:model="form.email" class="{{ $inp }}">
                  @error('form.email') <span class="text-red-400 text-xs font-semibold">{{ $message }}</span> @enderror
                </label>
                <label class="{{ $lbl }}">Status *
                  <select wire:model="form.status" class="{{ $inp }}">
                    @foreach(\App\Livewire\Admin\BookingsTable::STATUSES as $s)
                      <option value="{{ $s }}" class="bg-[#0A0A0A]">{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                  </select>
                  @error('form.status') <span class="text-red-400 text-xs font-semibold">{{ $message }}</span> @enderror
                </label>
                @if($isGen)
                  <label class="{{ $lbl }}">Kategori
                    <select wire:model="form.customer_category" class="{{ $inp }}">
                      <option value="" class="bg-[#0A0A0A]">-</option>
                      @foreach(\App\Models\Lead::CUSTOMER_CATEGORIES as $cat)
                        <option value="{{ $cat }}" class="bg-[#0A0A0A]">{{ trans('form.categories.'.$cat.'.title', [], 'ms') }}</option>
                      @endforeach
                    </select>
                  </label>
                  <label class="{{ $lbl }}">Syarikat
                    <input type="text" wire:model="form.company_name" class="{{ $inp }}">
                  </label>
                  <label class="{{ $lbl }}">Lesen
                    <select wire:model="form.driver_license" class="{{ $inp }}">
                      <option value="" class="bg-[#0A0A0A]">-</option>
                      <option value="malaysia" class="bg-[#0A0A0A]">{{ trans('form.license_options.malaysia', [], 'ms') }}</option>
                      <option value="international" class="bg-[#0A0A0A]">{{ trans('form.license_options.international', [], 'ms') }}</option>
                    </select>
                  </label>
                @endif
              </div>

              <p class="m-0 mt-2 text-[11.5px] font-bold tracking-[0.18em] text-white/40">BUTIRAN SEWAAN</p>
              <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <label class="{{ $lbl }}">Tujuan
                  <input type="text" wire:model="form.purpose" class="{{ $inp }}">
                </label>
                @if($isGen)
                  <label class="{{ $lbl }}">Negeri Ambil
                    <select wire:model="form.pickup_state" class="{{ $inp }}">
                      <option value="" class="bg-[#0A0A0A]">-</option>
                      @foreach(trans('form.states', [], 'ms') as $st)
                        <option value="{{ $st }}" class="bg-[#0A0A0A]">{{ $st }}</option>
                      @endforeach
                    </select>
                  </label>
                  <label class="{{ $lbl }}">Lokasi Ambil
                    <input type="text" wire:model="form.pickup_location" class="{{ $inp }}">
                  </label>
                  <label class="{{ $lbl }}">Lokasi Pulang
                    <input type="text" wire:model="form.return_location" class="{{ $inp }}">
                  </label>
                @else
                  <label class="{{ $lbl }}">Datang Dari
                    <input type="text" wire:model="form.origin" class="{{ $inp }}">
                  </label>
                  <label class="{{ $lbl }}">Airport
                    <input type="text" wire:model="form.airport" class="{{ $inp }}">
                  </label>
                  <label class="{{ $lbl }}">Destinasi
                    <input type="text" wire:model="form.destination" class="{{ $inp }}">
                  </label>
                @endif
                <label class="{{ $lbl }}">{{ $isGen ? 'Tarikh Ambil' : 'Tarikh Ketibaan' }}
                  <input type="date" wire:model="form.arrival_date" class="{{ $inp }} [color-scheme:dark]">
                  @error('form.arrival_date') <span class="text-red-400 text-xs font-semibold">{{ $message }}</span> @enderror
                </label>
                <label class="{{ $lbl }}">{{ $isGen ? 'Masa Ambil' : 'Masa Ketibaan' }}
                  <input type="time" wire:model="form.arrival_time" class="{{ $inp }} [color-scheme:dark]">
                  @error('form.arrival_time') <span class="text-red-400 text-xs font-semibold">{{ $message }}</span> @enderror
                </label>
                <label class="{{ $lbl }}">{{ $isGen ? 'Tarikh Pulang' : 'Tarikh Tamat Sewa' }}
                  <input type="date" wire:model="form.end_date" class="{{ $inp }} [color-scheme:dark]">
                  @error('form.end_date') <span class="text-red-400 text-xs font-semibold">{{ $message }}</span> @enderror
                </label>
                <label class="{{ $lbl }}">Kenderaan
                  <input type="text" wire:model="form.vehicle_name_snapshot" class="{{ $inp }}">
                </label>
                @unless($isGen)
                  <label class="{{ $lbl }}">Model Lain
                    <input type="text" wire:model="form.other_vehicle_model" class="{{ $inp }}">
                  </label>
                  <label class="{{ $lbl }}">Luggage
                    <input type="text" wire:model="form.luggage" class="{{ $inp }}">
                  </label>
                @endunless
                <label class="{{ $lbl }}">Penumpang
                  <input type="number" min="1" max="50" wire:model="form.passengers" class="{{ $inp }}">
                  @error('form.passengers') <span class="text-red-400 text-xs font-semibold">{{ $message }}</span> @enderror
                </label>
              </div>

              <label class="{{ $lbl }}">Catatan
                <textarea wire:model="form.notes" rows="3" class="{{ $inp }} resize-y"></textarea>
              </label>
            </div>
            <div class="sticky bottom-0 flex flex-wrap gap-2.5 justify-end bg-[#141414] border-t border-white/8 px-5 md:px-7 py-4">
              <button type="button" wire:click="cancelEdit" class="border border-white/15 hover:border-white/40 text-white text-[13px] font-bold px-4 py-2.5 rounded-lg">Batal</button>
              <button type="submit" wire:loading.attr="disabled" class="bg-brand-red hover:opacity-90 text-white text-[13px] font-bold px-5 py-2.5 rounded-lg">
                <span wire:loading.remove wire:target="saveLead">Simpan Perubahan</span>
                <span wire:loading wire:target="saveLead">Menyimpan...</span>
              </button>
            </div>
          </form>
        @endif
      </div>
    </div>
  @endif
</div>
