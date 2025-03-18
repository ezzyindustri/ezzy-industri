<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QualityCheckDetail extends Model
{
    protected $fillable = [
        'quality_check_id',
        'parameter',
        'standard_value',
        'measured_value',
        'tolerance_min',
        'tolerance_max',
        'status'
    ];

    public function qualityCheck()
    {
        return $this->belongsTo(QualityCheck::class);
    }
}