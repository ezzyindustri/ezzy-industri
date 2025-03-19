<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Machine extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'description',
        'location',
        'status',
        'oee_target',
        'alert_enabled',
        'alert_email',
        'alert_phone' // Tambahkan field alert_phone
    ];

    protected $casts = [
        'status' => 'string',
        'oee_target' => 'decimal:2',
        'alert_enabled' => 'boolean'
    ];

    public function getStatusAttribute($value)
    {
        return $value ?? 'active';
    }

    public function sops()
    {
        return $this->hasMany(Sop::class);
    }
    
    public function oeeRecords()
    {
        return $this->hasMany(OeeRecord::class);
    }
    
    public function productions()
    {
        return $this->hasMany(Production::class);
    }
}