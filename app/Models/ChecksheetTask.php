<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChecksheetTask extends Model
{
    protected $fillable = [
        'machine_id',
        'type',
        'frequency',
        'task_name',
        'description',
        'standard_value',
        'min_value',
        'max_value',
        'requires_photo',
        'order'
    ];

    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }
}