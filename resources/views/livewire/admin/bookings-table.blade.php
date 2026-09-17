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
  </div>

  @if($leads->isEmpty())
    <p class="m-0 text-[13px] text-white/45">Tiada tempahan dijumpai.</p>
  @else
    <div class="overflow-x-auto bg-[#141414] border border-white/8 rounded-2xl">
      <table class="w-full border-collapse text-[13px]">
        <thead>
          <tr class="text-left text-white/50">
            <th class="p-3.5 font-bold">Nama</th>
            <th class="p-3.5 font-bold">Telefon</th>
            <th class="p-3.5 font-bold">Kenderaan</th>
            <th class="p-3.5 font-bold">Tarikh Ketibaan</th>
            <th class="p-3.5 font-bold">Status</th>
            <th class="p-3.5"></th>
          </tr>
        </thead>
        <tbody>
          @foreach($leads as $lead)
            <tr class="border-t border-white/6">
              <td class="p-3.5">{{ $lead->full_name }}</td>
              <td class="p-3.5">{{ $lead->phone }}</td>
              <td class="p-3.5">{{ $lead->vehicle_name_snapshot }}</td>
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
        @foreach([
          'Nama' => $selectedLead->full_name, 'Telefon' => $selectedLead->phone, 'Email' => $selectedLead->email ?: '-',
          'Datang Dari' => $selectedLead->origin, 'Airport' => $selectedLead->airport,
          'Tarikh Ketibaan' => optional($selectedLead->arrival_date)->format('d/m/Y').' '.$selectedLead->arrival_time,
          'Tarikh Tamat Sewa' => optional($selectedLead->end_date)->format('d/m/Y'),
          'Tujuan' => $selectedLead->purpose, 'Kenderaan' => $selectedLead->vehicle_name_snapshot,
          'Penumpang' => $selectedLead->passengers, 'Luggage' => $selectedLead->luggage,
          'Destinasi' => $selectedLead->destination, 'Catatan' => $selectedLead->notes ?: '-',
        ] as $label => $value)
          <div>
            <p class="m-0 mb-0.5 text-[11.5px] font-bold tracking-wide text-white/45">{{ $label }}</p>
            <p class="m-0">{{ $value }}</p>
          </div>
        @endforeach
      </div>
    </div>
  @endif
</div>
