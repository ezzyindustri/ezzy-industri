<?php

namespace App\Models;

use App\Notifications\OeeAlertNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;

class OeeRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'machine_id',
        'production_id',
        'shift_id',
        'date',
        'planned_production_time',
        'operating_time',
        'downtime_problems',
        'downtime_maintenance',
        'total_downtime',
        'total_output',
        'good_output',
        'defect_count',
        'ideal_cycle_time',
        'availability_rate',
        'performance_rate',
        'quality_rate',
        'oee_score'
    ];

    protected $casts = [
        'date' => 'date',
        'availability_rate' => 'float',
        'performance_rate' => 'float',
        'quality_rate' => 'float',
        'oee_score' => 'float',
        'ideal_cycle_time' => 'float'
    ];

    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    public function production()
    {
        return $this->belongsTo(Production::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($oeeRecord) {
            $machine = $oeeRecord->machine;
            Log::info('OEE Record created', [
                'machine' => $machine?->name,
                'alert_enabled' => $machine?->alert_enabled,
                'oee_score' => $oeeRecord->oee_score,
                'oee_target' => $machine?->oee_target,
                'alert_email' => $machine?->alert_email
            ]);
            
            if ($machine && $machine->alert_enabled && $oeeRecord->oee_score < $machine->oee_target) {
                Notification::route('mail', $machine->alert_email)
                    ->notify(new OeeAlertNotification($machine, $oeeRecord->oee_score, $machine->oee_target));
            }
        });
    }
}