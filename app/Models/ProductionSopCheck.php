<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionSopCheck extends Model
{
    protected $fillable = [
        'production_id',
        'sop_step_id',  // kita gunakan ini, bukan sop_id
        'nilai',
        'status'
    ];

    public function production()
    {
        return $this->belongsTo(Production::class);
    }

    public function step()
    {
        return $this->belongsTo(SopStep::class, 'sop_step_id');
    }

    // Ubah relasi sop menjadi melalui step
    public function sop()
    {
        return $this->belongsTo(Sop::class)->through('step');
    }
}