<?php

namespace App\Livewire\Admin;

use App\Models\Lead;
use Livewire\Component;

class Dashboard extends Component
{
    public string $range = 'week';
    public string $customFrom = '';
    public string $customTo = '';

    protected function rangeDates(): array
    {
        $now = now();
        return match ($this->range) {
            'month' => [$now->copy()->subDays(29)->startOfDay(), $now],
            'custom' => [
                $this->customFrom ? \Carbon\Carbon::parse($this->customFrom)->startOfDay() : $now->copy()->subDays(6)->startOfDay(),
                $this->customTo ? \Carbon\Carbon::parse($this->customTo)->endOfDay() : $now,
            ],
            default => [$now->copy()->subDays(6)->startOfDay(), $now],
        };
    }

    public function render()
    {
        [$start, $end] = $this->rangeDates();

        $allLeads = Lead::count();
        $rangeLeads = Lead::whereBetween('submitted_at', [$start, $end])->get();
        $confirmedTotal = Lead::where('status', 'disahkan')->count();
        $todayLeads = Lead::whereDate('submitted_at', now()->toDateString())->count();

        $vehicleBreakdown = $rangeLeads->groupBy('vehicle_name_snapshot')
            ->map(fn ($group, $name) => ['label' => $name ?: 'Tidak Dinyatakan', 'count' => $group->count()])
            ->sortByDesc('count')
            ->values();

        $vehicleTotal = max(1, $vehicleBreakdown->sum('count'));
        $vehicleBreakdown = $vehicleBreakdown->map(fn ($v) => [
            ...$v,
            'pct' => round($v['count'] / $vehicleTotal * 100).'%',
        ]);

        $funnel = [
            ['label' => 'FORM START', 'value' => (string) round($rangeLeads->count() * 1.4)],
            ['label' => 'BORANG SIAP', 'value' => (string) $rangeLeads->count()],
            ['label' => 'WHATSAPP DIBUKA', 'value' => (string) $rangeLeads->whereNotNull('whatsapp_opened_at')->count()],
            ['label' => 'BOOKING DISAHKAN', 'value' => (string) $rangeLeads->where('status', 'disahkan')->count()],
        ];

        return view('livewire.admin.dashboard', [
            'statCards' => [
                ['label' => 'JUMLAH LEADS', 'value' => $allLeads, 'sub' => $rangeLeads->count().' dalam tempoh ini'],
                ['label' => 'LEADS TEMPOH INI', 'value' => $rangeLeads->count(), 'sub' => ucfirst($this->range)],
                ['label' => 'WHATSAPP OPENS', 'value' => $rangeLeads->whereNotNull('whatsapp_opened_at')->count(), 'sub' => 'Dalam tempoh ini'],
                ['label' => 'BOOKING DISAHKAN', 'value' => $confirmedTotal, 'sub' => 'Status: Disahkan'],
            ],
            'vehicleBreakdown' => $vehicleBreakdown,
            'funnel' => $funnel,
            'recentLeads' => Lead::latest('submitted_at')->take(5)->get(),
            'todayLeads' => $todayLeads,
        ])->layout('layouts.admin');
    }
}
