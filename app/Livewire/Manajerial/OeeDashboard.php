<?php

namespace App\Livewire\Manajerial;

use Livewire\Component;
use App\Models\Machine;
use App\Models\Shift;
use App\Models\Production;
use App\Models\QualityCheck;
use Carbon\Carbon;
use App\Models\Product;
use Illuminate\Support\Facades\Log; 
use App\Models\OeeRecord;
use App\Exports\OeeExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class OeeDashboard extends Component
{
    public $selectedDate;
    public $selectedShift;
    public $machines;
    public $shifts;
    public $startDate;
    public $endDate;

    public function mount()
    {
        $this->startDate = now()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
        $this->loadData();
    }

    // public function exportToExcel()
    // {
    //     return Excel::download(
    //         new OeeExport($this->startDate, $this->endDate),
    //         'oee-report-' . now()->format('Y-m-d') . '.xlsx'
    //     );
    // }

    public function loadData()
    {
        $this->machines = Machine::with(['productions' => function($query) {
            $query->whereBetween('created_at', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay()
            ])
            ->when($this->selectedShift, function($q) {
                $q->where('shift_id', $this->selectedShift);
            });
        }])->get()->map(function($machine) {
            // Get all productions for the date range
            $productions = $machine->productions;
            
            if ($productions->isEmpty()) {
                return [
                    'id' => $machine->id,
                    'name' => $machine->name,
                    'availability_rate' => 0,
                    'performance_rate' => 0,
                    'quality_rate' => 0,
                    'oee_score' => 0
                ];
            }

            // Calculate averages for the date range
            $totalAvailability = 0;
            $totalPerformance = 0;
            $totalQuality = 0;
            $totalOee = 0;

            foreach ($productions as $production) {
                $shift = Shift::find($production->shift_id);
                $product = Product::find($production->product_id);
                
                // Calculate total downtime
                $downtimeFromProblems = $production->problems()->sum('duration') ?? 0;
                $downtimeFromDowntimes = $production->productionDowntimes()->sum('duration_minutes') ?? 0;
                $totalDowntime = $downtimeFromProblems + $downtimeFromDowntimes;

                // Calculate rates
                $plannedTime = $shift->planned_operation_time;
                $operatingTime = $plannedTime - $totalDowntime;
                $availabilityRate = ($operatingTime / $plannedTime) * 100;

                $idealCycleTime = $product->cycle_time;
                $actualOutput = $production->total_production ?? 0;
                $performanceRate = $operatingTime > 0 ? (($actualOutput * $idealCycleTime) / $operatingTime) * 100 : 0;

                $defectCount = QualityCheck::where('production_id', $production->id)->sum('defect_count') ?? 0;
                $goodOutput = $actualOutput - $defectCount;
                $qualityRate = ($goodOutput / ($actualOutput ?: 1)) * 100;

                $oeeScore = ($availabilityRate * $performanceRate * $qualityRate) / 10000;

                // Add to totals
                $totalAvailability += $availabilityRate;
                $totalPerformance += $performanceRate;
                $totalQuality += $qualityRate;
                $totalOee += $oeeScore;

                // Save individual record
                OeeRecord::updateOrCreate(
                    [
                        'machine_id' => $machine->id,
                        'production_id' => $production->id,
                        'shift_id' => $production->shift_id,
                        'date' => Carbon::parse($production->created_at)->format('Y-m-d'),
                    ],
                    [
                        'planned_production_time' => $plannedTime,
                        'operating_time' => $operatingTime,
                        'downtime_problems' => $downtimeFromProblems,
                        'downtime_maintenance' => $downtimeFromDowntimes,
                        'total_downtime' => $totalDowntime,
                        'total_output' => $actualOutput,
                        'good_output' => $goodOutput,
                        'defect_count' => $defectCount,
                        'ideal_cycle_time' => $idealCycleTime,
                        'availability_rate' => round($availabilityRate, 2),
                        'performance_rate' => round($performanceRate, 2),
                        'quality_rate' => round($qualityRate, 2),
                        'oee_score' => round($oeeScore, 2)
                    ]
                );
            }

            // Calculate averages
            $productionCount = $productions->count();
            return [
                'id' => $machine->id,
                'name' => $machine->name,
                'availability_rate' => round($totalAvailability / $productionCount, 2),
                'performance_rate' => round($totalPerformance / $productionCount, 2),
                'quality_rate' => round($totalQuality / $productionCount, 2),
                'oee_score' => round($totalOee / $productionCount, 2)
            ];
        });

        $this->shifts = Shift::where('status', 'active')->get();
    }

    // Add these methods to handle date changes
    public function updatedStartDate()
    {
        $this->loadData();
    }

    public function updatedEndDate()
    {
        $this->loadData();
    }

    public function updatedSelectedShift()
    {
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.manajerial.oee-dashboard');
    }

    public function exportToPdf()
    {
        $data = [
            'machines' => $this->machines,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate
        ];
    
        $pdf = Pdf::loadView('pdf.oee-pdf', $data)->setPaper('a4', 'portrait');
        
        return response($pdf->output())
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="oee-report-' . now()->format('Y-m-d') . '.pdf"');
    }
}