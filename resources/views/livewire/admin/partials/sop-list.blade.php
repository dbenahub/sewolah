<ul class="adm-sop" role="list">
  @foreach($items as $key => [$label, $auto, $source])
    @php $info = $lead->checkInfo($key); @endphp
    <li wire:key="sop-{{ $key }}">
      <button type="button" wire:click="toggleCheck('{{ $key }}')" @class(['adm-sop__item', 'is-done' => $info]) aria-pressed="{{ $info ? 'true' : 'false' }}">
        <span class="adm-sop__box">@if($info)<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12.5 4.5 4.5L19 7.5"/></svg>@endif</span>
        <span class="adm-sop__text">
          <span class="adm-sop__label">{{ $label }}@if($auto)<span class="adm-sop__auto" title="Ditanda automatik oleh sistem">AUTO</span>@endif</span>
          <span class="adm-sop__meta">{{ $source }}@if($info) · {{ $info['by'] ?? 'Sistem' }} · {{ \Illuminate\Support\Carbon::parse($info['at'])->timezone(config('app.timezone'))->format('d/m/Y H:i') }}@endif</span>
        </span>
      </button>
    </li>
  @endforeach
</ul>
