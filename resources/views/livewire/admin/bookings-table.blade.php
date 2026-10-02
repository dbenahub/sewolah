<div class="grid gap-6">
  <div class="flex flex-wrap gap-3 justify-between items-center">
    <h1 class="m-0 text-2xl font-extrabold">Tempahan &amp; Pelanggan</h1>
    <button wire:click="exportCsv" class="bg-white/10 text-white text-xs font-bold px-3.5 py-2 rounded-lg">Export CSV</button>
  </div>

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
              <td class="p-3.5">{{ $lead->full_name }}</td>
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
                <button wire:click="viewLead({{ $lead->id }})" class="bg-transparent border border-white/15 text-white text-xs font-semibold px-3 py-1.5 rounded-lg">Lihat</button>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div>{{ $leads->links() }}</div>
  @endif

  @if($selectedLead)
    <div class="bg-[#141414] border border-white/8 rounded-2xl p-6 grid gap-3.5">
      <div class="flex justify-between items-center">
        <p class="m-0 text-[17px] font-extrabold">Detail Pelanggan</p>
        <button wire:click="closeLead" class="bg-transparent border-none text-white/50 text-[13px]">Tutup ✕</button>
      </div>
      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3.5 text-[13.5px]">
        @php
          $selTime = $selectedLead->arrival_time ? substr((string) $selectedLead->arrival_time, 0, 5) : '';
          $detailRows = $selectedLead->isGeneral() ? [
            'No. Rujukan' => $selectedLead->referenceNumber(), 'Sumber' => $selectedLead->sourceLabel(),
            'Kategori' => $selectedLead->customerCategoryLabel('ms'), 'Nama' => $selectedLead->full_name,
            'Syarikat' => $selectedLead->company_name ?: '-', 'Telefon' => $selectedLead->phone, 'Email' => $selectedLead->email ?: '-',
            'Lesen' => $selectedLead->driver_license ? trans('form.license_options.'.$selectedLead->driver_license, [], 'ms') : '-',
            'Tujuan' => $selectedLead->purpose, 'Negeri Ambil' => $selectedLead->pickup_state,
            'Lokasi Ambil' => $selectedLead->pickup_location, 'Lokasi Pulang' => $selectedLead->return_location ?: 'Sama',
            'Tarikh/Masa Ambil' => optional($selectedLead->arrival_date)->format('d/m/Y').' '.$selTime,
            'Tarikh Pulang' => optional($selectedLead->end_date)->format('d/m/Y'),
            'Kenderaan' => $selectedLead->vehicle_name_snapshot, 'Penumpang' => $selectedLead->passengers,
            'Catatan' => $selectedLead->notes ?: '-',
          ] : [
            'No. Rujukan' => $selectedLead->referenceNumber(), 'Sumber' => $selectedLead->sourceLabel(),
            'Nama' => $selectedLead->full_name, 'Telefon' => $selectedLead->phone, 'Email' => $selectedLead->email ?: '-',
            'Datang Dari' => $selectedLead->origin, 'Airport' => $selectedLead->airport,
            'Tarikh Ketibaan' => optional($selectedLead->arrival_date)->format('d/m/Y').' '.$selTime,
            'Tarikh Tamat Sewa' => optional($selectedLead->end_date)->format('d/m/Y'),
            'Tujuan' => $selectedLead->purpose, 'Kenderaan' => $selectedLead->vehicle_name_snapshot,
            'Penumpang' => $selectedLead->passengers, 'Luggage' => $selectedLead->luggage,
            'Destinasi' => $selectedLead->destination, 'Catatan' => $selectedLead->notes ?: '-',
          ];
        @endphp
        @foreach($detailRows as $label => $value)
          <div>
            <p class="m-0 mb-0.5 text-[11.5px] font-bold tracking-wide text-white/45">{{ $label }}</p>
            <p class="m-0">{{ $value }}</p>
          </div>
        @endforeach
      </div>
    </div>
  @endif
</div>
