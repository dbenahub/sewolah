<?php

namespace App\Livewire\Admin;

use App\Models\Lead;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class BookingsTable extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $sourceFilter = '';
    public ?int $selectedLeadId = null;

    /** 'view' or 'edit' — controls the detail modal */
    public string $mode = 'view';

    /** Editable copy of the selected lead */
    public array $form = [];

    public ?string $flash = null;

    public const STATUSES = ['baru', 'dihubungi', 'quotation_dihantar', 'disahkan', 'batal'];

    protected const EDITABLE = [
        'full_name', 'phone', 'email', 'company_name', 'customer_category', 'driver_license', 'status',
        'purpose', 'origin', 'airport', 'pickup_state', 'pickup_location', 'return_location',
        'arrival_date', 'arrival_time', 'end_date', 'vehicle_name_snapshot', 'other_vehicle_model',
        'passengers', 'luggage', 'destination', 'notes',
    ];

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
        if (in_array($status, self::STATUSES, true)) {
            Lead::whereKey($leadId)->update(['status' => $status]);
        }
    }

    public function viewLead(int $leadId)
    {
        if (! Lead::whereKey($leadId)->exists()) {
            return;
        }

        $this->resetValidation();
        $this->selectedLeadId = $leadId;
        $this->mode = 'view';
        $this->flash = null;
    }

    public function editLead(int $leadId)
    {
        $lead = Lead::find($leadId);
        if (! $lead) {
            return;
        }

        $this->resetValidation();
        $this->selectedLeadId = $lead->id;
        $this->mode = 'edit';
        $this->flash = null;

        $form = [];
        foreach (self::EDITABLE as $field) {
            $value = $lead->{$field};
            $form[$field] = match ($field) {
                'arrival_date', 'end_date' => $value?->format('Y-m-d') ?? '',
                'arrival_time' => $value ? substr((string) $value, 0, 5) : '',
                default => $value === null ? '' : (string) $value,
            };
        }
        $this->form = $form;
    }

    public function cancelEdit()
    {
        $this->resetValidation();
        $this->mode = 'view';
    }

    protected function formRules(): array
    {
        return [
            'form.full_name' => ['required', 'string', 'max:150'],
            'form.phone' => ['required', 'string', 'max:30'],
            'form.email' => ['nullable', 'email', 'max:150'],
            'form.company_name' => ['nullable', 'string', 'max:150'],
            'form.customer_category' => ['nullable', Rule::in(Lead::CUSTOMER_CATEGORIES)],
            'form.driver_license' => ['nullable', Rule::in(['malaysia', 'international'])],
            'form.status' => ['required', Rule::in(self::STATUSES)],
            'form.purpose' => ['nullable', 'string', 'max:100'],
            'form.origin' => ['nullable', 'string', 'max:150'],
            'form.airport' => ['nullable', 'string', 'max:100'],
            'form.pickup_state' => ['nullable', 'string', 'max:60'],
            'form.pickup_location' => ['nullable', 'string', 'max:150'],
            'form.return_location' => ['nullable', 'string', 'max:150'],
            'form.arrival_date' => ['nullable', 'date_format:Y-m-d'],
            'form.arrival_time' => ['nullable', 'date_format:H:i'],
            'form.end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:form.arrival_date'],
            'form.vehicle_name_snapshot' => ['nullable', 'string', 'max:150'],
            'form.other_vehicle_model' => ['nullable', 'string', 'max:150'],
            'form.passengers' => ['nullable', 'integer', 'min:1', 'max:50'],
            'form.luggage' => ['nullable', 'string', 'max:50'],
            'form.destination' => ['nullable', 'string', 'max:150'],
            'form.notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function saveLead()
    {
        $lead = Lead::find($this->selectedLeadId);
        if (! $lead) {
            $this->closeLead();
            return;
        }

        $this->validate($this->formRules(), [
            'required' => 'Medan ini wajib diisi.',
            'email' => 'Format e-mel tidak sah.',
            'date_format' => 'Format tidak sah.',
            'after_or_equal' => 'Tarikh pulang mesti sama atau selepas tarikh ambil.',
            'integer' => 'Mesti nombor.',
            'min' => 'Nilai terlalu kecil.',
            'max' => 'Nilai terlalu panjang/besar.',
        ]);

        $data = [];
        foreach (self::EDITABLE as $field) {
            $value = $this->form[$field] ?? null;
            $data[$field] = ($value === '' || $value === null) ? null : $value;
        }
        // Required columns must never be null
        $data['full_name'] = trim((string) $data['full_name']);
        $data['phone'] = trim((string) $data['phone']);
        $data['status'] = $data['status'] ?? 'baru';
        $data['passengers'] = $data['passengers'] !== null ? (int) $data['passengers'] : null;

        $lead->update($data);

        $this->mode = 'view';
        $this->flash = 'Tempahan '.$lead->referenceNumber().' berjaya dikemas kini.';
    }

    public function deleteLead(int $leadId)
    {
        $lead = Lead::find($leadId);
        if (! $lead) {
            return;
        }

        $ref = $lead->referenceNumber();
        $lead->delete();

        if ($this->selectedLeadId === $leadId) {
            $this->selectedLeadId = null;
            $this->mode = 'view';
        }

        $this->flash = 'Tempahan '.$ref.' telah dipadam.';
    }

    public function closeLead()
    {
        $this->resetValidation();
        $this->selectedLeadId = null;
        $this->mode = 'view';
        $this->form = [];
    }

    public function dismissFlash()
    {
        $this->flash = null;
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
