<?php

namespace App\Livewire\Admin;

use App\Models\Lead;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Component;

class Dashboard extends Component
{
    /** '7', '30', '90' or 'custom' */
    public string $range = '30';
    public string $customFrom = '';
    public string $customTo = '';

    public const STATUS_LABELS = [
        'baru' => 'Baru',
        'dihubungi' => 'Dihubungi',
        'quotation_dihantar' => 'Quotation dihantar',
        'disahkan' => 'Disahkan',
        'batal' => 'Batal',
    ];

    public function setRange(string $range): void
    {
        if (in_array($range, ['7', '30', '90', 'custom'], true)) {
            $this->range = $range;
            if ($range === 'custom' && ! $this->customFrom) {
                $this->customFrom = now()->subDays(29)->toDateString();
                $this->customTo = now()->toDateString();
            }
        }
    }

    /** @return array{0: Carbon, 1: Carbon} */
    protected function period(): array
    {
        $tz = config('app.timezone');
        $today = now($tz)->endOfDay();

        if ($this->range === 'custom') {
            try {
                $from = $this->customFrom ? Carbon::parse($this->customFrom, $tz)->startOfDay() : $today->copy()->subDays(29)->startOfDay();
                $to = $this->customTo ? Carbon::parse($this->customTo, $tz)->endOfDay() : $today->copy();
            } catch (\Throwable $e) {
                $from = $today->copy()->subDays(29)->startOfDay();
                $to = $today->copy();
            }
            if ($from->gt($to)) {
                [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
            }
            // Cap at one year to keep the chart readable
            if ($from->diffInDays($to) > 366) {
                $from = $to->copy()->subDays(365)->startOfDay();
            }

            return [$from, $to];
        }

        $days = (int) $this->range;

        return [$today->copy()->subDays($days - 1)->startOfDay(), $today];
    }

    protected function topWithOther(Collection $items, int $limit = 5): Collection
    {
        $sorted = $items->sortDesc();
        $top = $sorted->take($limit);
        $rest = $sorted->slice($limit)->sum();
        if ($rest > 0) {
            $top->put('Lain-lain', $rest);
        }

        return $top;
    }

    public function render()
    {
        $tz = config('app.timezone');
        [$start, $end] = $this->period();
        $days = (int) $start->copy()->startOfDay()->diffInDays($end->copy()->startOfDay()) + 1;
        $prevEnd = $start->copy()->subSecond();
        $prevStart = $start->copy()->subDays($days);

        $leads = Lead::whereBetween('submitted_at', [$start, $end])->get();
        $prevCount = Lead::whereBetween('submitted_at', [$prevStart, $prevEnd])->count();
        $total = $leads->count();
        $delta = $prevCount > 0 ? round((($total - $prevCount) / $prevCount) * 100) : null;

        // Daily series (zero-filled)
        $byDay = $leads->groupBy(fn ($l) => optional($l->submitted_at)->timezone($tz)->toDateString())->map->count();
        $series = [];
        for ($d = $start->copy()->startOfDay(); $d->lte($end); $d->addDay()) {
            $key = $d->toDateString();
            $series[] = ['date' => $key, 'label' => $d->locale('ms')->translatedFormat('j M'), 'dow' => $d->locale('ms')->translatedFormat('D'), 'count' => (int) ($byDay[$key] ?? 0)];
        }
        $seriesMax = max(1, collect($series)->max('count'));
        // Clean y-axis ticks
        $step = max(1, (int) ceil($seriesMax / 4));
        $yMax = $step * 4;

        $confirmed = $leads->where('status', 'disahkan')->count();
        $cancelled = $leads->where('status', 'batal')->count();
        $conversion = $total > 0 ? round($confirmed / $total * 100) : 0;
        $pendingAll = Lead::where('status', 'baru')->count();

        $today = now($tz)->startOfDay();
        $upcoming = Lead::whereNotNull('arrival_date')
            ->whereBetween('arrival_date', [$today->toDateString(), $today->copy()->addDays(14)->toDateString()])
            ->where('status', '!=', 'batal')
            ->orderBy('arrival_date')->orderBy('arrival_time')
            ->get();

        $statusDist = collect(self::STATUS_LABELS)->map(fn ($label, $key) => [
            'key' => $key, 'label' => $label, 'count' => $leads->where('status', $key)->count(),
        ])->values();

        $sourceGeneral = $leads->where('source', 'general')->count();
        $sourceOutstation = $total - $sourceGeneral;

        $categoryDist = $leads->where('source', 'general')->whereNotNull('customer_category')
            ->groupBy('customer_category')
            ->map(fn ($g, $key) => ['label' => trans('form.categories.'.$key.'.title', [], 'ms'), 'count' => $g->count()])
            ->sortByDesc('count')->values();

        $vehicleDist = $this->topWithOther(
            $leads->groupBy(fn ($l) => $l->vehicle_name_snapshot ?: 'Tidak dinyatakan')->map->count()
        );

        $locationDist = $this->topWithOther(
            $leads->groupBy(fn ($l) => $l->pickup_state ?: ($l->source === 'outstation' ? 'KL/Selangor (Outstation)' : 'Tidak dinyatakan'))->map->count()
        );

        return view('livewire.admin.dashboard', [
            'start' => $start,
            'end' => $end,
            'days' => $days,
            'total' => $total,
            'prevCount' => $prevCount,
            'delta' => $delta,
            'series' => $series,
            'yMax' => $yMax,
            'yStep' => $step,
            'confirmed' => $confirmed,
            'cancelled' => $cancelled,
            'conversion' => $conversion,
            'pendingAll' => $pendingAll,
            'allTime' => Lead::count(),
            'upcoming' => $upcoming,
            'statusDist' => $statusDist,
            'statusMax' => max(1, $statusDist->max('count')),
            'sourceGeneral' => $sourceGeneral,
            'sourceOutstation' => $sourceOutstation,
            'categoryDist' => $categoryDist,
            'vehicleDist' => $vehicleDist,
            'locationDist' => $locationDist,
            'recentLeads' => Lead::latest('submitted_at')->take(6)->get(),
        ])->layout('layouts.admin');
    }
}
