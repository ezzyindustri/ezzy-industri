<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QualityCheck extends Model
{
    protected $fillable = [
        'production_id',
        'user_id',
        'sample_size',
        'notes',
        'check_time',
        'status',
        'defect_count',    // Tambahkan ini
        'defect_type',     // Tambahkan ini
        'defect_notes'     // Tambahkan ini
    ];

    protected $casts = [
        'check_time' => 'datetime'
    ];

    public function production()
    {
        return $this->belongsTo(Production::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(QualityCheckDetail::class);
    }
}