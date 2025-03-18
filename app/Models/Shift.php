<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Shift extends Model
{
    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'planned_operation_time',
        'status'
    ];

    public static function getCurrentShift()
    {
        $now = Carbon::now();
        $currentTime = $now->format('H:i:s');

        return self::whereRaw("TIME(?) BETWEEN TIME(start_time) AND TIME(end_time)", [$currentTime])
            ->first();
    }

    public function productions()
    {
        return $this->hasMany(Production::class);
    }
}