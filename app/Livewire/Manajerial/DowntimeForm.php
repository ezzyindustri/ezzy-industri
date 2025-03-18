<?php

namespace App\Livewire\Manajerial;

use Livewire\Component;
use App\Models\DowntimeRecord;
use App\Models\OeeRecord;

class DowntimeForm extends Component
{
    public $oeeRecordId;
    public $startTime;
    public $endTime;
    public $type;
    public $reason;
    
    protected $rules = [
        'startTime' => 'required',
        'endTime' => 'required|after:startTime',
        'type' => 'required|in:planned,unplanned',
        'reason' => 'required|max:255'
    ];

    public function saveDowntime()
    {
        $this->validate();

        $start = \Carbon\Carbon::parse($this->startTime);
        $end = \Carbon\Carbon::parse($this->endTime);
        
        $duration = $end->diffInMinutes($start);

        DowntimeRecord::create([
            'oee_record_id' => $this->oeeRecordId,
            'start_time' => $this->startTime,
            'end_time' => $this->endTime,
            'duration' => $duration,
            'type' => $this->type,
            'reason' => $this->reason
        ]);

        // Recalculate OEE
        $oeeRecord = OeeRecord::find($this->oeeRecordId);
        $oeeRecord->downtime = $oeeRecord->downtimeRecords->sum('duration');
        $oeeRecord->calculateOEE();

        $this->reset(['startTime', 'endTime', 'type', 'reason']);
        $this->dispatch('downtime-added');
    }

    public function render()
    {
        return view('livewire.manajerial.downtime-form');
    }
}