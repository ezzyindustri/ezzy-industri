<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChecksheetHistory extends Model
{
    protected $fillable = [
        'checksheet_type',
        'checksheet_id',
        'user_id',
        'action',
        'old_values',
        'new_values'
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array'
    ];

    public function checksheet()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}