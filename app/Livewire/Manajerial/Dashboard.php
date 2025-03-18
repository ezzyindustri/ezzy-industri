<?php

namespace App\Livewire\Manajerial;

use Livewire\Component;
use App\Models\Machine;
use App\Models\Production;
use App\Models\ProductionProblem;
use App\Models\ProductionDowntime;
use Carbon\Carbon;

class Dashboard extends Component
{
    public $totalMachines;
    public $activeProductions;
    public $pendingProblems;
    public $unresolvedProblems;
    public $todayDowntime;

    public function mount()
    {
        $this->refreshData();
    }

    public function refreshData()
    {
        $this->totalMachines = Machine::count();
        $this->activeProductions = Production::where('status', 'running')->count() ?? 0;
        $this->pendingProblems = ProductionProblem::where('status', 'waiting')->count() ?? 0;
        $this->unresolvedProblems = ProductionProblem::whereNotIn('status', ['resolved'])->count() ?? 0;
        
        $todayDowntimeMinutes = ProductionDowntime::whereDate('created_at', Carbon::today())
            ->sum('duration_minutes') ?? 0;
        $this->todayDowntime = round($todayDowntimeMinutes / 60, 1) . ' Jam';
    }

    public function render()
    {
        $this->refreshData(); // Memastikan data selalu fresh saat render
        return view('livewire.manajerial.dashboard');
    }
}