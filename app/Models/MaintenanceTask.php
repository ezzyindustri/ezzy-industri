<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_name',
        'description',
        'maintenance_type',
        'frequency',
        'machine_id',
        'requires_photo',
        'standard_value',
        'is_active',
        'shift_ids',
        'preferred_time',
        'schedule_config'
    ];

    protected $casts = [
        'shift_ids' => 'array',
        'requires_photo' => 'boolean',
        'is_active' => 'boolean',
        'is_daily' => 'boolean',
        'schedule_config' => 'array'
    ];

    public function getNextScheduleAttribute()
    {
        $lastEntry = $this->checksheetEntries()->latest()->first();
        if ($lastEntry) {
            return Carbon::parse($lastEntry->created_at)
                ->addDay()
                ->setTimezone('Asia/Jakarta');
        }
        return null;
    }


    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    public function schedules()
    {
        return $this->hasMany(MaintenanceSchedule::class, 'task_id');
    }

    public function shifts()
    {
        return Shift::whereIn('id', $this->shift_ids ?? [])->get();
    }

    public function checksheetEntries()
    {
        return $this->hasMany(ChecksheetEntry::class, 'task_id');
    }
}