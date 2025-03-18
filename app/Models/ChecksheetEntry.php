<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChecksheetEntry extends Model
{
    protected $fillable = [
        'task_id',
        'production_id',
        'machine_id',
        'shift_id',
        'user_id',
        'result',
        'notes',
        'photo_path'
    ];

    protected $casts = [
        'check_date' => 'date'
    ];
    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    public function task()
    {
        return $this->belongsTo(MaintenanceTask::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function checksheetTask()
    {
        return $this->belongsTo(ChecksheetTask::class, 'task_id');
    }
}