@php
    $n = count($series);
    $sparkMax = max(1, collect($series)->max('count'));
    $pts = collect($series)->values()->map(function ($p, $i) use ($n, $sparkMax) {
        $x = $n > 1 ? round($i / ($n - 1) * 100, 2) : 50;
        $y = round(46 - ($p['count'] / $sparkMax) * 40, 2);
        return $x.','.$y;
    })->implode(' ');
    $labelEvery = $n <= 7 ? 1 : ($n <= 31 ? 5 : ($n <= 92 ? 15 : 30));
    $xLabels = collect($series)->values()->filter(fn ($p, $i) => $i % $labelEvery === 0 || $i === $n - 1);
    $vehMax = max(1, $vehicleDist->max() ?? 1);
    $locMax = max(1, $locationDist->max() ?? 1);
    $catMax = max(1, $categoryDist->max('count') ?? 1);
    $srcTotal = max(1, $sourceGeneral + $sourceOutstation);
    $rangeLabel = match ($range) { '7' => '7 hari', '30' => '30 hari', '90' => '90 hari', default => $start->format('d/m/Y').' – '.$end->format('d/m/Y') };
@endphp
<div class="adm-grid" style="gap:22px">
  {{-- Head + filters --}}
  <div class="adm-head">
    <div>
      <h1>Dashboard</h1>
      <p>Prestasi permohonan SEWOLAH · {{ $start->format('d M Y') }} – {{ $end->format('d M Y') }}</p>
    </div>
    <div class="adm-head__actions">
      <div class="adm-seg" role="tablist" aria-label="Tempoh">
        @foreach(['7' => '7 Hari', '30' => '30 Hari', '90' => '90 Hari', 'custom' => 'Custom'] as $key => $label)
          <button type="button" wire:click="setRange('{{ $key }}')" @class(['is-active' => $range === (string) $key])>{{ $label }}</button>
        @endforeach
      </div>
      @if($range === 'custom')
        <input type="date" wire:model.live="customFrom" class="adm-input adm-input--sm" style="width:auto" aria-label="Dari">
        <input type="date" wire:model.live="customTo" class="adm-input adm-input--sm" style="width:auto" aria-label="Hingga">
      @endif
    </div>
  </div>

  {{-- Hero figure + KPI tiles --}}
  <div class="adm-hero">
    <div class="adm-card adm-herofig">
      <p class="adm-herofig__label">Permohonan diterima · {{ $rangeLabel }}</p>
      <p class="adm-herofig__value">{{ number_format($total) }}</p>
      <div class="adm-herofig__meta">
        @if($delta === null)
          <span class="adm-delta adm-delta--flat">Baharu</span>
        @elseif($delta > 0)
          <span class="adm-delta adm-delta--up">▲ {{ $delta }}%</span>
        @elseif($delta < 0)
          <span class="adm-delta adm-delta--down">▼ {{ abs($delta) }}%</span>
        @else
          <span class="adm-delta adm-delta--flat">0%</span>
        @endif
        <span>berbanding {{ number_format($prevCount) }} dalam {{ $days }} hari sebelumnya</span>
      </div>
      <svg class="spark" viewBox="0 0 100 50" preserveAspectRatio="none" aria-hidden="true">
        <polygon points="0,50 {{ $pts }} 100,50" fill="rgba(255,59,69,0.14)"/>
        <polyline points="{{ $pts }}" fill="none" stroke="#FF3B45" stroke-width="2" stroke-linejoin="round" stroke-linecap="round" vector-effect="non-scaling-stroke"/>
      </svg>
    </div>

    <div class="adm-kpis">
      <div class="adm-card adm-kpi">
        <div class="adm-kpi__top">
          <span class="adm-kpi__label">Perlu tindakan</span>
          <span class="adm-kpi__icon adm-kpi__icon--accent"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M12 8v5M12 16.5v.5M10.3 3.9 2.6 17.3A2 2 0 0 0 4.3 20h15.4a2 2 0 0 0 1.7-2.7L13.7 3.9a2 2 0 0 0-3.4 0Z"/></svg></span>
        </div>
        <p class="adm-kpi__value">{{ number_format($pendingAll) }}</p>
        <p class="adm-kpi__sub">Permohonan berstatus "Baru"</p>
        <a href="{{ route('admin.bookings', ['statusFilter' => 'baru']) }}" class="adm-hint" style="font-weight:700;color:var(--accent-ink);text-decoration:none;margin-top:4px">Semak sekarang →</a>
      </div>

      <div class="adm-card adm-kpi">
        <div class="adm-kpi__top">
          <span class="adm-kpi__label">Kadar pengesahan</span>
          <span class="adm-kpi__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12.5 4.5 4.5L19 7.5"/></svg></span>
        </div>
        <p class="adm-kpi__value">{{ $conversion }}%</p>
        <div class="adm-meter" role="img" aria-label="{{ $conversion }} peratus disahkan"><span style="width: {{ $conversion }}%"></span></div>
        <p class="adm-kpi__sub">{{ $confirmed }} disahkan daripada {{ $total }} permohonan</p>
      </div>

      <div class="adm-card adm-kpi">
        <div class="adm-kpi__top">
          <span class="adm-kpi__label">Ambil 14 hari akan datang</span>
          <span class="adm-kpi__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6.5h16v13H4zM4 10.5h16M8.5 3.5v5M15.5 3.5v5"/></svg></span>
        </div>
        <p class="adm-kpi__value">{{ $upcoming->count() }}</p>
        <p class="adm-kpi__sub">Tempahan aktif (tidak termasuk batal)</p>
      </div>

      <div class="adm-card adm-kpi">
        <div class="adm-kpi__top">
          <span class="adm-kpi__label">Jumlah keseluruhan</span>
          <span class="adm-kpi__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19V5M4 19h16M8 15v-4M12 15V8M16 15v-6"/></svg></span>
        </div>
        <p class="adm-kpi__value">{{ number_format($allTime) }}</p>
        <p class="adm-kpi__sub">Semua permohonan sejak mula · {{ $cancelled }} batal dalam tempoh ini</p>
      </div>
    </div>
  </div>

  {{-- Daily column chart + pipeline --}}
  <div class="adm-grid adm-grid--main">
    <section class="adm-card">
      <div class="adm-card__head">
        <div>
          <h2 class="adm-card__title">Permohonan harian</h2>
          <p class="adm-card__sub">Bilangan permohonan diterima setiap hari · {{ $rangeLabel }}</p>
        </div>
      </div>
      <div class="adm-colchart" role="img" aria-label="Carta permohonan harian">
        <div class="adm-colchart__y" aria-hidden="true">
          @for($t = 0; $t <= 4; $t++)
            <span style="bottom: {{ $t * 25 }}%">{{ $yStep * $t }}</span>
          @endfor
        </div>
        <div class="adm-colchart__plot">
          @for($t = 1; $t <= 4; $t++)
            <div class="adm-colchart__grid" style="bottom: {{ $t * 25 }}%"></div>
          @endfor
          <div class="adm-colchart__cols">
            @foreach($series as $p)
              @php($h = $yMax > 0 ? round($p['count'] / $yMax * 100, 2) : 0)
              <div class="adm-col" data-tip="{{ $p['count'] }} permohonan" data-tip-sub="{{ $p['dow'] }}, {{ $p['label'] }}">
                <div @class(['adm-col__bar', 'adm-col__bar--zero' => $p['count'] === 0]) style="height: {{ $h }}%"></div>
              </div>
            @endforeach
          </div>
        </div>
      </div>
      <div class="adm-colchart__x" aria-hidden="true">
        <span></span>
        <div class="adm-colchart__xl">
          @foreach($xLabels as $p)<span>{{ $p['label'] }}</span>@endforeach
        </div>
      </div>
    </section>

    <section class="adm-card">
      <div class="adm-card__head">
        <div>
          <h2 class="adm-card__title">Status permohonan</h2>
          <p class="adm-card__sub">Aliran kerja dalam tempoh dipilih</p>
        </div>
      </div>
      <div class="adm-pipeline">
        @foreach($statusDist as $s)
          <div class="adm-pipe" data-tip="{{ $s['count'] }} permohonan" data-tip-sub="{{ $s['label'] }}">
            <span><span class="adm-pill adm-pill--{{ $s['key'] }}">{{ $s['label'] }}</span></span>
            <div class="adm-pipe__track"><div class="adm-pipe__fill" style="width: {{ round($s['count'] / $statusMax * 100, 2) }}%"></div></div>
            <span class="adm-pipe__val">{{ $s['count'] }}</span>
          </div>
        @endforeach
      </div>

      <div style="margin-top:26px">
        <p class="adm-section-label" style="margin-bottom:10px">Sumber permohonan</p>
        <div class="adm-split" role="img" aria-label="Borang utama {{ $sourceGeneral }}, Outstation {{ $sourceOutstation }}">
          @if($sourceGeneral + $sourceOutstation === 0)
            <span style="width:100%;background:var(--track)"></span>
          @else
            @if($sourceGeneral)<span class="s1" style="width: {{ $sourceGeneral / $srcTotal * 100 }}%" data-tip="{{ $sourceGeneral }} permohonan" data-tip-sub="Borang utama (/form)"></span>@endif
            @if($sourceOutstation)<span class="s2" style="width: {{ $sourceOutstation / $srcTotal * 100 }}%" data-tip="{{ $sourceOutstation }} permohonan" data-tip-sub="Outstation"></span>@endif
          @endif
        </div>
        <div class="adm-legend">
          <div class="adm-legend__item"><span class="adm-legend__key s1"></span>Borang utama <strong>{{ $sourceGeneral }}</strong> <small>{{ round($sourceGeneral / $srcTotal * 100) }}%</small></div>
          <div class="adm-legend__item"><span class="adm-legend__key s2"></span>Outstation <strong>{{ $sourceOutstation }}</strong> <small>{{ round($sourceOutstation / $srcTotal * 100) }}%</small></div>
        </div>
      </div>
    </section>
  </div>

  {{-- Breakdown bars --}}
  <div class="adm-grid adm-grid--3">
    <section class="adm-card">
      <div class="adm-card__head"><div><h2 class="adm-card__title">Kenderaan diminati</h2><p class="adm-card__sub">Pilihan kenderaan pemohon</p></div></div>
      <div class="adm-hbars">
        @forelse($vehicleDist as $label => $count)
          <div class="adm-hbar">
            <div class="adm-hbar__top"><span class="adm-hbar__label" title="{{ $label }}">{{ $label }}</span><span class="adm-hbar__val">{{ $count }}<small>{{ round($count / max(1, $total) * 100) }}%</small></span></div>
            <div class="adm-hbar__track"><div class="adm-hbar__fill" style="width: {{ round($count / $vehMax * 100, 2) }}%"></div></div>
          </div>
        @empty
          <p class="adm-empty" style="padding:20px 0">Tiada data dalam tempoh ini.</p>
        @endforelse
      </div>
    </section>

    <section class="adm-card">
      <div class="adm-card__head"><div><h2 class="adm-card__title">Kategori pelanggan</h2><p class="adm-card__sub">Borang utama (/form)</p></div></div>
      <div class="adm-hbars">
        @forelse($categoryDist as $c)
          <div class="adm-hbar">
            <div class="adm-hbar__top"><span class="adm-hbar__label">{{ $c['label'] }}</span><span class="adm-hbar__val">{{ $c['count'] }}<small>{{ round($c['count'] / max(1, $sourceGeneral) * 100) }}%</small></span></div>
            <div class="adm-hbar__track"><div class="adm-hbar__fill" style="width: {{ round($c['count'] / $catMax * 100, 2) }}%"></div></div>
          </div>
        @empty
          <p class="adm-empty" style="padding:20px 0">Tiada data dalam tempoh ini.</p>
        @endforelse
      </div>
    </section>

    <section class="adm-card">
      <div class="adm-card__head"><div><h2 class="adm-card__title">Lokasi ambil</h2><p class="adm-card__sub">Negeri pengambilan kenderaan</p></div></div>
      <div class="adm-hbars">
        @forelse($locationDist as $label => $count)
          <div class="adm-hbar">
            <div class="adm-hbar__top"><span class="adm-hbar__label" title="{{ $label }}">{{ $label }}</span><span class="adm-hbar__val">{{ $count }}<small>{{ round($count / max(1, $total) * 100) }}%</small></span></div>
            <div class="adm-hbar__track"><div class="adm-hbar__fill" style="width: {{ round($count / $locMax * 100, 2) }}%"></div></div>
          </div>
        @empty
          <p class="adm-empty" style="padding:20px 0">Tiada data dalam tempoh ini.</p>
        @endforelse
      </div>
    </section>
  </div>

  {{-- Recent + upcoming --}}
  <div class="adm-grid adm-grid--main">
    <section class="adm-card adm-card--flush">
      <div class="adm-card__head" style="padding:20px 22px 0;margin-bottom:14px">
        <div><h2 class="adm-card__title">Permohonan terkini</h2><p class="adm-card__sub">6 permohonan terakhir</p></div>
        <a href="{{ route('admin.bookings') }}" class="adm-btn adm-btn--sm">Lihat semua</a>
      </div>
      @if($recentLeads->isEmpty())
        <p class="adm-empty">Tiada permohonan lagi.</p>
      @else
        <div class="adm-table-wrap">
          <table class="adm-table">
            <thead><tr><th>Rujukan</th><th>Pelanggan</th><th>Kenderaan</th><th>Dihantar</th><th>Status</th></tr></thead>
            <tbody>
              @foreach($recentLeads as $lead)
                <tr>
                  <td class="adm-ref">{{ $lead->referenceNumber() }}</td>
                  <td><strong>{{ $lead->full_name }}</strong><span class="adm-sub">{{ $lead->isGeneral() ? ($lead->customerCategoryLabel('ms') ?? 'Borang utama') : 'Outstation' }}</span></td>
                  <td>{{ $lead->vehicle_name_snapshot ?: '-' }}</td>
                  <td class="num">{{ optional($lead->submitted_at)->format('d/m/Y H:i') }}</td>
                  <td><span class="adm-pill adm-pill--{{ $lead->status }}">{{ \App\Livewire\Admin\Dashboard::STATUS_LABELS[$lead->status] ?? $lead->status }}</span></td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </section>

    <section class="adm-card">
      <div class="adm-card__head"><div><h2 class="adm-card__title">Jadual ambil kenderaan</h2><p class="adm-card__sub">14 hari akan datang</p></div></div>
      @if($upcoming->isEmpty())
        <p class="adm-empty" style="padding:24px 0">Tiada pengambilan dijadualkan.</p>
      @else
        <div class="adm-list">
          @foreach($upcoming->take(6) as $u)
            <div class="adm-list__item">
              <div class="adm-list__date"><b>{{ $u->arrival_date->format('j') }}</b><small>{{ $u->arrival_date->locale('ms')->translatedFormat('M') }}</small></div>
              <div class="adm-list__main">
                <strong>{{ $u->full_name }}</strong>
                <span>{{ $u->vehicle_name_snapshot ?: '-' }} · {{ $u->pickup_location ?: ($u->airport ?: '-') }}{{ $u->arrival_time ? ' · '.substr((string) $u->arrival_time, 0, 5) : '' }}</span>
              </div>
              <span class="adm-pill adm-pill--{{ $u->status }}">{{ \App\Livewire\Admin\Dashboard::STATUS_LABELS[$u->status] ?? $u->status }}</span>
            </div>
          @endforeach
        </div>
      @endif
    </section>
  </div>
</div>
