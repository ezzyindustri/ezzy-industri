<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sop extends Model
{
    protected $fillable = [
        'no_sop',
        'nama',
        'kategori',
        'deskripsi',
        'versi',
        'machine_id',
        'product_id',
        'created_by',
        'approved_by',
        'created_date',
        'approved_at',
        'approval_status'
    ];

    protected $casts = [
        'created_date' => 'datetime',
        'approved_at' => 'datetime',
        'interval_check' => 'integer'
    ];

    public function steps()
    {
        return $this->hasMany(SopStep::class)->orderBy('urutan');
    }

    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}