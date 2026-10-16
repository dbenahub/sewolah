<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadEvent extends Model
{
    protected $fillable = ['lead_id', 'user_id', 'type', 'message'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
