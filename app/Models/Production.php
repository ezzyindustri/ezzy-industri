<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\QualityCheck;

class Production extends Model
{
    protected $fillable = [
        'user_id',
        'machine_id',
        'machine_name',
        'machine',
        'product',
        'product_id',
        'shift_id',  // Add this
        'start_time',
        'end_time',
        'status',
        'total_production',
        'defect_count',
        'defect_type',
        'notes',
        'target_per_shift'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function problems()
    {
        return $this->hasMany(ProductionProblem::class);
    }
    public function machine()
    {
    return $this->belongsTo(Machine::class);
    }
    public function checks()
    {
        return $this->hasMany(ProductionCheck::class);
    }

    public function sopChecks()
    {
        return $this->hasMany(ProductionSopCheck::class);
    }

    public function qualityChecks()
    {
        return $this->hasMany(QualityCheck::class);
    }

    public function productionDowntimes()
    {
        return $this->hasMany(ProductionDowntime::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }

    public function checksheetEntries()
    {
        return $this->hasMany(ChecksheetEntry::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}