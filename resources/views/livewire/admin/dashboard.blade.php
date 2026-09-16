<div class="grid gap-6">
  <div>
    <h1 class="m-0 mb-1 text-2xl font-extrabold">Dashboard</h1>
    <p class="m-0 text-[13px] text-white/45">Gambaran prestasi tempahan SEWOLAH</p>
  </div>

  <div class="flex gap-2 flex-wrap">
    @foreach(['week' => 'Mingguan', 'month' => 'Bulanan', 'custom' => 'Custom'] as $key => $label)
      <button wire:click="$set('range', '{{ $key }}')"
        class="{{ $range === $key ? 'bg-brand-red border-brand-red' : 'bg-transparent border-white/15' }} text-white border text-[12.5px] font-bold px-3.5 py-2 rounded-lg">{{ $label }}</button>
    @endforeach
    @if($range === 'custom')
      <input type="date" wire:model.live="customFrom" class="px-2.5 py-2 rounded-lg border border-white/15 bg-[#0A0A0A] text-white text-xs">
      <input type="date" wire:model.live="customTo" class="px-2.5 py-2 rounded-lg border border-white/15 bg-[#0A0A0A] text-white text-xs">
    @endif
  </div>

  <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
    @foreach($statCards as $c)
      <div class="bg-[#141414] border border-white/8 rounded-2xl p-5 grid gap-2.5 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-1 h-full bg-brand-red"></div>
        <p class="m-0 text-[11.5px] font-bold tracking-wide text-white/50">{{ $c['label'] }}</p>
        <p class="m-0 text-3xl font-extrabold">{{ $c['value'] }}</p>
        <p class="m-0 text-[11.5px] text-white/40">{{ $c['sub'] }}</p>
      </div>
    @endforeach
  </div>

  <div class="bg-[#141414] border border-white/8 rounded-2xl p-6 grid gap-4">
    <p class="m-0 text-[15px] font-bold">Kenderaan Diminati</p>
    @forelse($vehicleBreakdown as $v)
      <div class="grid gap-1.5">
        <div class="flex justify-between text-[12.5px]">
          <span class="text-white/75">{{ $v['label'] }}</span>
          <span class="text-white/50">{{ $v['count'] }}</span>
        </div>
        <div class="bg-[#0A0A0A] rounded-full h-1.5 overflow-hidden">
          <div class="h-full bg-brand-red rounded-full" style="width: {{ $v['pct'] }}"></div>
        </div>
      </div>
    @empty
      <p class="m-0 text-[12.5px] text-white/40">Belum ada data tempahan dalam tempoh ini.</p>
    @endforelse
  </div>

  <div class="bg-[#141414] border border-white/8 rounded-2xl p-6 grid gap-4">
    <p class="m-0 text-[15px] font-bold">Corong Penukaran (Conversion Funnel)</p>
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
      @foreach($funnel as $f)
        <div class="grid gap-2 text-center p-4.5 bg-[#0A0A0A] rounded-xl border border-white/6">
          <p class="m-0 text-xl font-extrabold text-brand-red">{{ $f['value'] }}</p>
          <p class="m-0 text-xs font-bold tracking-wide">{{ $f['label'] }}</p>
        </div>
      @endforeach
    </div>
  </div>

  <div class="bg-[#141414] border border-white/8 rounded-2xl p-5.5 grid gap-3.5">
    <p class="m-0 text-[15px] font-bold">Tempahan Terkini</p>
    @forelse($recentLeads as $lead)
      <div class="flex gap-3 items-center p-2.5 bg-[#0A0A0A] rounded-lg border border-white/6 text-[13px] text-white/75">
        {{ $lead->full_name }} — {{ $lead->vehicle_name_snapshot }} ({{ optional($lead->submitted_at)->format('d/m/Y') }})
      </div>
    @empty
      <p class="m-0 text-[13px] text-white/40">Tiada tempahan lagi.</p>
    @endforelse
  </div>
</div>
