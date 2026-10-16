<?php

namespace App\Livewire\Admin;

use App\Models\CarRate;
use App\Support\CarCatalog;
use Illuminate\Validation\Rule;
use Livewire\Component;

/** Admin → Harga Kereta: the rental price list (retail shown to customers; agent/VIP internal). */
class CarRates extends Component
{
    public string $search = '';
    public string $categoryFilter = '';

    public ?int $editingId = null;
    public bool $showForm = false;
    public string $brand = '';
    public string $model = '';
    public string $category = 'sedan';
    public string $agentRate = '';
    public string $vipRate = '';
    public string $retailMin = '';
    public string $retailMax = '';
    public string $listPrice = '';
    public string $deposit = '';
    public string $notes = '';
    public bool $isActive = true;

    public ?string $flash = null;

    protected function rules(): array
    {
        return [
            'brand' => ['required', 'string', 'max:60'],
            'model' => ['required', 'string', 'max:120'],
            'category' => ['required', Rule::in(array_keys(CarCatalog::CATEGORIES))],
            'agentRate' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'vipRate' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'retailMin' => ['required', 'integer', 'min:1', 'max:100000'],
            'retailMax' => ['nullable', 'integer', 'gte:retailMin', 'max:100000'],
            'listPrice' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'deposit' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'notes' => ['nullable', 'string', 'max:250'],
        ];
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $r = CarRate::findOrFail($id);
        $this->editingId = $r->id;
        $this->brand = $r->brand;
        $this->model = $r->model;
        $this->category = $r->category;
        $this->agentRate = (string) ($r->agent_rate ?? '');
        $this->vipRate = (string) ($r->vip_rate ?? '');
        $this->retailMin = (string) $r->retail_min;
        $this->retailMax = (string) ($r->retail_max ?? '');
        $this->listPrice = (string) ($r->list_price ?? '');
        $this->deposit = (string) ($r->deposit ?? '');
        $this->notes = (string) ($r->notes ?? '');
        $this->isActive = $r->is_active;
        $this->showForm = true;
        $this->resetErrorBag();
    }

    public function save(): void
    {
        $this->validate(null, ['retailMax.gte' => 'Harga maksimum mesti ≥ harga minimum.']);
        $n = fn ($v) => $v === '' ? null : (int) $v;
        $data = [
            'brand' => trim($this->brand),
            'model' => trim($this->model),
            'category' => $this->category,
            'agent_rate' => $n($this->agentRate),
            'vip_rate' => $n($this->vipRate),
            'retail_min' => (int) $this->retailMin,
            'retail_max' => $n($this->retailMax) ?? (int) $this->retailMin,
            'list_price' => $n($this->listPrice),
            'deposit' => $n($this->deposit),
            'notes' => $this->notes !== '' ? $this->notes : null,
            'is_active' => $this->isActive,
        ];
        if ($this->editingId) {
            CarRate::whereKey($this->editingId)->update($data);
            $this->flash = $data['brand'].' '.$data['model'].' dikemas kini.';
        } else {
            $data['sort_order'] = (int) CarRate::max('sort_order') + 10;
            CarRate::create($data);
            $this->flash = $data['brand'].' '.$data['model'].' ditambah.';
        }
        $this->resetForm();
    }

    public function toggleActive(int $id): void
    {
        $r = CarRate::findOrFail($id);
        $r->update(['is_active' => ! $r->is_active]);
    }

    public function delete(int $id): void
    {
        $r = CarRate::findOrFail($id);
        $name = $r->name();
        $r->delete();
        $this->flash = $name.' dipadam.';
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->reset(['editingId', 'brand', 'model', 'agentRate', 'vipRate', 'retailMin', 'retailMax', 'listPrice', 'deposit', 'notes', 'showForm']);
        $this->category = 'sedan';
        $this->isActive = true;
        $this->resetErrorBag();
    }

    public function render()
    {
        $rates = CarRate::query()
            ->when($this->search !== '', function ($q) {
                $term = '%'.$this->search.'%';
                $q->where(fn ($w) => $w->where('brand', 'like', $term)->orWhere('model', 'like', $term));
            })
            ->when($this->categoryFilter !== '', fn ($q) => $q->where('category', $this->categoryFilter))
            ->orderBy('brand')->orderBy('sort_order')->orderBy('model')
            ->get();

        return view('livewire.admin.car-rates', [
            'rates' => $rates,
            'categories' => CarCatalog::CATEGORIES,
            'brands' => CarRate::distinct()->orderBy('brand')->pluck('brand'),
        ])->layout('layouts.admin');
    }
}
