<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'category', 'image_path', 'tags', 'cta_label',
        'is_featured', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function tagsFor(string $locale): array
    {
        return $this->tags[$locale] ?? $this->tags['ms'] ?? [];
    }

    public function imageUrl(): string
    {
        return $this->image_path
            ? asset('storage/'.$this->image_path)
            : asset('images/vehicles/placeholder.jpg');
    }
}
