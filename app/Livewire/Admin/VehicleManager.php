<?php

namespace App\Livewire\Admin;

use App\Models\Vehicle;
use Livewire\Component;
use Livewire\WithFileUploads;

class VehicleManager extends Component
{
    use WithFileUploads;

    public $editingId = null;
    public string $name = '';
    public string $category = '';
    public string $tagsMs = '';
    public string $tagsEn = '';
    public string $ctaLabel = '';
    public bool $isFeatured = true;
    public bool $isActive = true;
    public $newImage = null;

    public function edit(int $id)
    {
        $v = Vehicle::findOrFail($id);
        $this->editingId = $v->id;
        $this->name = $v->name;
        $this->category = $v->category;
        $this->tagsMs = implode(', ', $v->tags['ms'] ?? []);
        $this->tagsEn = implode(', ', $v->tags['en'] ?? []);
        $this->ctaLabel = $v->cta_label;
        $this->isFeatured = $v->is_featured;
        $this->isActive = $v->is_active;
    }

    public function resetForm()
    {
        $this->reset(['editingId', 'name', 'category', 'tagsMs', 'tagsEn', 'ctaLabel', 'newImage']);
        $this->isFeatured = true;
        $this->isActive = true;
    }

    public function save()
    {
        $this->validate([
            'name' => ['required', 'string', 'max:150'],
            'category' => ['required', 'string', 'max:100'],
        ]);

        $data = [
            'name' => $this->name,
            'category' => $this->category,
            'cta_label' => $this->ctaLabel,
            'tags' => [
                'ms' => array_filter(array_map('trim', explode(',', $this->tagsMs))),
                'en' => array_filter(array_map('trim', explode(',', $this->tagsEn))),
            ],
            'is_featured' => $this->isFeatured,
            'is_active' => $this->isActive,
        ];

        if ($this->newImage) {
            $data['image_path'] = $this->newImage->store('vehicles', 'public');
        }

        Vehicle::updateOrCreate(['id' => $this->editingId], $data);
        $this->resetForm();
    }

    public function delete(int $id)
    {
        Vehicle::whereKey($id)->delete();
    }

    public function render()
    {
        return view('livewire.admin.vehicle-manager', [
            'vehicles' => Vehicle::orderBy('sort_order')->get(),
        ])->layout('layouts.admin');
    }
}
