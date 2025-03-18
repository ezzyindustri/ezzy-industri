<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionCheck extends Model
{
    protected $fillable = [
        'production_id',
        'maintenance_task_id',
        'status',
        'notes',
        'photo_path'
    ];

    public function production()
    {
        return $this->belongsTo(Production::class);
    }

    public function maintenanceTask()
    {
        return $this->belongsTo(MaintenanceTask::class);
    }
}