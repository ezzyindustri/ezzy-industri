<?php

namespace App\Livewire\Manajerial;

use Livewire\Component;
use App\Models\Machine;
use App\Models\OeeRecord;
use App\Models\Production;
use App\Models\Shift;
use App\Models\Product;
use App\Models\QualityCheck;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class OeeDetail extends Component
{
    public $machine;
    public $selectedPeriod = 'daily';
    public $averageAvailability;
    public $averagePerformance;
    public $averageQuality;
    public $chartData;
    public $oeeScore;
    
    // Properti untuk perhitungan OEE
    public $plannedProductionTime = 0;
    public $operatingTime = 0;
    public $downtimeProblems = 0;
    public $downtimeMaintenance = 0;
    public $totalDowntime = 0;
    public $downtime = 0;         // Tambahan properti yang kurang
    public $idealCycleTime = 0;
    public $totalOutput = 0;
    public $defectCount = 0;
    public $goodOutput = 0;

    public function mount($machineId)
    {
        $this->machine = Machine::findOrFail($machineId);
        $this->loadData();
    }

    public function loadData()
    {
        $query = Production::where('machine_id', $this->machine->id)
            ->with(['shift', 'product', 'problems', 'productionDowntimes', 'qualityChecks']);

        // Filter berdasarkan periode
        switch ($this->selectedPeriod) {
            case 'daily':
                $query->whereDate('created_at', Carbon::today());
                break;
            case 'weekly':
                $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                break;
            case 'monthly':
                $query->whereMonth('created_at', Carbon::now()->month)
                      ->whereYear('created_at', Carbon::now()->year);
                break;
        }

        $productions = $query->get();

        // Reset calculation variables
        $this->plannedProductionTime = 0;
        $this->operatingTime = 0;
        $this->totalOutput = 0;
        $this->goodOutput = 0;

        foreach ($productions as $production) {
            $shift = $production->shift;
            $product = Product::find($production->product_id);

            if (!$shift || !$product) {
                continue;
            }

            // Update perhitungan downtime
            $this->downtimeProblems = $production->problems()->sum('duration') ?? 0;
            $this->downtimeMaintenance = $production->productionDowntimes()->sum('duration_minutes') ?? 0;
            $this->totalDowntime = $this->downtimeProblems + $this->downtimeMaintenance;

            // Update perhitungan waktu
            $this->plannedProductionTime += $shift->planned_operation_time ?? 0;
            $this->operatingTime += ($shift->planned_operation_time - $this->totalDowntime);
            
            // Update data produksi
            $this->idealCycleTime = $product->cycle_time ?? 0;
            $this->totalOutput += $production->total_production ?? 0;
            
            // Update data kualitas
            $this->defectCount += $production->qualityChecks->sum('defect_count') ?? 0;
            $this->goodOutput += ($production->total_production - $this->defectCount);
        }

        // Get OEE Records
        $records = OeeRecord::where('machine_id', $this->machine->id)
            ->when($this->selectedPeriod === 'daily', function($q) {
                return $q->whereDate('date', Carbon::today());
            })
            ->when($this->selectedPeriod === 'weekly', function($q) {
                return $q->whereBetween('date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
            })
            ->when($this->selectedPeriod === 'monthly', function($q) {
                return $q->whereMonth('date', Carbon::now()->month)
                    ->whereYear('date', Carbon::now()->year);
            })
            ->orderBy('date', 'asc')
            ->get();

        // Calculate averages from OEE Records
        $this->averageAvailability = round($records->avg('availability_rate') ?? 0, 2);
        $this->averagePerformance = round($records->avg('performance_rate') ?? 0, 2);
        $this->averageQuality = round($records->avg('quality_rate') ?? 0, 2);
        $this->oeeScore = round($records->avg('oee_score') ?? 0, 2);

        // Get the latest record for detailed calculations
        $latestRecord = $records->last();
        // Di dalam method loadData(), bagian pengambilan data dari latestRecord
        if ($latestRecord) {
            $this->plannedProductionTime = $latestRecord->planned_production_time;
            $this->operatingTime = $latestRecord->operating_time;
            $this->downtimeProblems = $latestRecord->downtime_problems;
            $this->downtimeMaintenance = $latestRecord->downtime_maintenance;
            $this->totalDowntime = $latestRecord->total_downtime;
            $this->downtime = $this->plannedProductionTime - $this->operatingTime;
            $this->totalOutput = $latestRecord->total_output;
            $this->goodOutput = $latestRecord->good_output;
            $this->defectCount = $latestRecord->defect_count;
            // Ambil ideal_cycle_time langsung dari OeeRecord
            $this->idealCycleTime = $latestRecord->ideal_cycle_time;
        }

        // Prepare chart data
        $this->chartData = [
            'labels' => $records->pluck('date')->map(fn($date) => Carbon::parse($date)->format('d/m/Y'))->toArray(),
            'availability' => $records->pluck('availability_rate')->toArray(),
            'performance' => $records->pluck('performance_rate')->toArray(),
            'quality' => $records->pluck('quality_rate')->toArray(),
            'oee' => $records->pluck('oee_score')->toArray()
        ];
    }

    public function updatedSelectedPeriod()
    {
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.manajerial.oee-detail');
    }
}