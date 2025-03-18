<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AmChecksheet extends Model
{
    protected $fillable = [
        'user_id',
        'machine_id',
        'shift_id',
        'check_date',
        'status',
        'notes',
        'before_photo',
        'after_photo',
        'submitted_at'
    ];

    protected $casts = [
        'check_date' => 'date',
        'submitted_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function histories()
    {
        return $this->morphMany(ChecksheetHistory::class, 'checksheet');
    }
}