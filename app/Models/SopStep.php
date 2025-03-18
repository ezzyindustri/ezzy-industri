<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SopStep extends Model
{
    protected $fillable = [
        'sop_id',
        'urutan',
        'judul',
        'deskripsi',
        'gambar_path',
        'is_checkpoint',
        'needs_standard',
        'nilai_standar',
        'toleransi_min',
        'toleransi_max',
        'measurement_type',
        'measurement_unit',
        'interval_value',
        'interval_unit'
    ];

    protected static $measurementUnits = [
        'length' => ['mm', 'cm', 'm'],
        'diameter' => ['mm', 'cm'],
        'weight' => ['g', 'kg'],
        'temperature' => ['°C', '°F'],
        'pressure' => ['Bar', 'PSI'],
        'angle' => ['degree'],
        'time' => ['s', 'min', 'hour'],
        'other' => ['unit']
    ];

    public static function getMeasurementUnits($type = null)
    {
        return $type ? (self::$measurementUnits[$type] ?? []) : self::$measurementUnits;
    }

    public function sop()
    {
        return $this->belongsTo(Sop::class);
    }
}