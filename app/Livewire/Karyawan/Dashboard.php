<?php

namespace App\Livewire\Karyawan;

use Livewire\Component;
use App\Models\Production;
use App\Models\ProductionDowntime;
use App\Models\ProductionProblem;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class Dashboard extends Component
{
    public $todayProduction;
    public $todayDefects;
    public $activeProduction;
    public $totalDowntime;
    public $recentDowntimes;
    public $recentProblems;

    public function mount()
    {
        $this->loadDashboardData();
    }

    public function loadDashboardData()
    {
        $today = Carbon::today();
        
        // Get today's production data
        $this->todayProduction = Production::where('user_id', Auth::id())
            ->whereDate('created_at', $today)
            ->sum('total_production');

        $this->todayDefects = Production::where('user_id', Auth::id())
            ->whereDate('created_at', $today)
            ->sum('defect_count');

        // Get active production
        $this->activeProduction = Production::where('user_id', Auth::id())
            ->whereIn('status', ['running', 'paused', 'problem'])
            ->first();

        // Calculate total downtime for today
        $this->totalDowntime = ProductionDowntime::whereHas('production', function($query) {
            $query->where('user_id', Auth::id());
        })
        ->whereDate('start_time', $today)
        ->sum('duration_minutes');

        // Get recent downtimes
        $this->recentDowntimes = ProductionDowntime::whereHas('production', function($query) {
            $query->where('user_id', Auth::id());
        })
        ->latest()
        ->take(5)
        ->get();

        // Get recent problems
        $this->recentProblems = ProductionProblem::whereHas('production', function($query) {
            $query->where('user_id', Auth::id());
        })
        ->latest()
        ->take(5)
        ->get();
    }

    public function render()
    {
        return view('livewire.karyawan.dashboard');
    }
}