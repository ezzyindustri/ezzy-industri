<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DowntimeRecord extends Model
{
    protected $fillable = [
        'oee_record_id',
        'start_time',
        'end_time',
        'duration',
        'type',
        'reason'
    ];

    public function oeeRecord()
    {
        return $this->belongsTo(OeeRecord::class);
    }
}