<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OeeAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_id',
        'machine_id',
        'oee_score',
        'target_oee',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'oee_score' => 'float',
        'target_oee' => 'float',
    ];

    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    public function production()
    {
        return $this->belongsTo(Production::class);
    }
}