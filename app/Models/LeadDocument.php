<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class LeadDocument extends Model
{
    public const TYPES = [
        'ic_front' => 'IC / Pasport (depan)',
        'ic_back' => 'IC (belakang)',
        'license' => 'Lesen memandu',
        'bill' => 'Bil utiliti (TNB / air / internet)',
        'other' => 'Dokumen sokongan',
        'receipt' => 'Resit bayaran',
    ];

    protected $fillable = ['lead_id', 'type', 'path', 'original_name', 'mime', 'size'];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function label(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function isImage(): bool
    {
        return str_starts_with((string) $this->mime, 'image/');
    }

    public function exists(): bool
    {
        return Storage::disk('local')->exists($this->path);
    }
}
