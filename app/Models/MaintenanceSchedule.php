<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceSchedule extends Model
{
    protected $fillable = [
        'machine_id',
        'task_id',
        'shift_id',
        'maintenance_type',
        'schedule_date',
        'status',
        'completed_at',
        'completed_by'
    ];

    protected $casts = [
        'schedule_date' => 'date',
        'completed_at' => 'datetime'
    ];

    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    public function task()
    {
        return $this->belongsTo(MaintenanceTask::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function completedBy()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}