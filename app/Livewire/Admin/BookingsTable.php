<?php

namespace App\Livewire\Admin;

use App\Models\Lead;
use Livewire\Component;
use Livewire\WithPagination;

class BookingsTable extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $sourceFilter = '';
    public ?int $selectedLeadId = null;

    protected $queryString = ['search', 'statusFilter', 'sourceFilter'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingSourceFilter()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updateStatus(int $leadId, string $status)
    {
        Lead::whereKey($leadId)->update(['status' => $status]);
    }

    public function viewLead(int $leadId)
    {
        $this->selectedLeadId = $leadId;
    }

    public function closeLead()
    {
        $this->selectedLeadId = null;
    }

    public function exportCsv()
    {
        $leads = Lead::orderByDesc('submitted_at')->get();

        return response()->streamDownload(function () use ($leads) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'No. Rujukan', 'Sumber', 'Kategori', 'Nama', 'Syarikat', 'Telefon', 'Email', 'Tujuan',
                'Negeri / Datang Dari', 'Lokasi Ambil', 'Kenderaan', 'Tarikh Ambil/Ketibaan', 'Tarikh Pulang',
                'Penumpang', 'Status', 'Tarikh Submit',
            ]);
            foreach ($leads as $lead) {
                fputcsv($out, [
                    $lead->referenceNumber(), $lead->sourceLabel(), $lead->customerCategoryLabel('ms'),
                    $lead->full_name, $lead->company_name, $lead->phone, $lead->email, $lead->purpose,
                    $lead->pickup_state ?: $lead->origin, $lead->pickup_location ?: $lead->airport,
                    $lead->vehicle_name_snapshot, optional($lead->arrival_date)->format('Y-m-d'),
                    optional($lead->end_date)->format('Y-m-d'), $lead->passengers, $lead->status, $lead->submitted_at,
                ]);
            }
            fclose($out);
        }, 'sewolah-leads-'.now()->format('Y-m-d').'.csv');
    }

    public function render()
    {
        $leads = Lead::query()
            ->when($this->search, fn ($q) => $q->where(fn ($q2) => $q2
                ->where('full_name', 'like', "%{$this->search}%")
                ->orWhere('phone', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%")
            ))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->sourceFilter, fn ($q) => $q->where('source', $this->sourceFilter))
            ->orderByDesc('submitted_at')
            ->paginate(15);

        return view('livewire.admin.bookings-table', [
            'leads' => $leads,
            'selectedLead' => $this->selectedLeadId ? Lead::find($this->selectedLeadId) : null,
        ])->layout('layouts.admin');
    }
}
