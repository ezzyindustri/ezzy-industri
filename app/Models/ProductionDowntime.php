<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class ProductionDowntime extends Model
{
    protected $fillable = [
        'production_id',
        'reason',
        'notes',
        'start_time',
        'end_time',
        'duration_minutes'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime'
    ];

    public function production()
    {
        return $this->belongsTo(Production::class);
    }

    protected static function booted()
    {
        static::creating(function ($downtime) {
            if ($downtime->end_time) {
                $start = Carbon::parse($downtime->start_time);
                $end = Carbon::parse($downtime->end_time);
                // Perbaikan perhitungan durasi
                $downtime->duration_minutes = $start->diffInMinutes($end);
            }
        });

        static::updating(function ($downtime) {
            if ($downtime->end_time) {
                $start = Carbon::parse($downtime->start_time);
                $end = Carbon::parse($downtime->end_time);
                // Perbaikan perhitungan durasi
                $downtime->duration_minutes = $start->diffInMinutes($end);
            }
        });
    }
}