<?php

namespace App\Livewire\Components;

use Livewire\Component;
use App\Models\Sop;

class MachineSopViewer extends Component
{
    public $machineId;
    public $sop;

    public function mount($machineId)
    {
        $this->machineId = $machineId;
        $this->loadSop();
    }

    public function loadSop()
    {
        if ($this->machineId) {
            $this->sop = Sop::where('machine_id', $this->machineId)
                ->with(['steps', 'creator', 'approver'])
                ->first();
        }
    }

    public function render()
    {
        return view('livewire.components.machine-sop-viewer');
    }
}