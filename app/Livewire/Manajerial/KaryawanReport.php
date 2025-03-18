<?php

namespace App\Livewire\Manajerial;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\DB; // Tambahkan ini
use Barryvdh\DomPDF\Facade\Pdf;


class KaryawanReport extends Component
{
    use WithPagination;

    public $selectedPeriod = 30;
    public $selectedDepartment;
    public $selectedStatus;
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $dateFrom;
    public $dateTo;
    public $search = ''; // Add this line for search functionality

    public function mount()
    {
        DB::enableQueryLog();
        
        // Set default period to 30 days
        $this->selectedPeriod = 30;
        $this->updateDateRange(30);
    }

    public function updatedSelectedPeriod($value)
    {
        $this->updateDateRange($value);
    }

    private function updateDateRange($days)
    {
        if ($days == 0) {
            // Today
            $today = now()->format('Y-m-d');
            $this->dateFrom = $today;
            $this->dateTo = $today;
        } else {
            // X days ago until now
            $this->dateFrom = now()->subDays($days)->format('Y-m-d');
            $this->dateTo = now()->format('Y-m-d');
        }
    }

    private function calculateMetrics($user)
    {
        $dateFrom = $this->dateFrom;
        $dateTo = $this->dateTo;

        // Quality Check Metrics
        $qcMetrics = $user->getQualityMetrics($dateFrom, $dateTo);
        
        // Maintenance Check Metrics
        $maintenanceMetrics = $user->getMaintenanceMetrics($dateFrom, $dateTo);
        
        // Production Metrics
        $productionMetrics = $user->getProductionMetrics($dateFrom, $dateTo);

        // Calculate Performance Rate
        $performanceRate = round(
            ($qcMetrics['compliance_rate'] + 
             $maintenanceMetrics['compliance_rate'] + 
             $productionMetrics['achievement_rate']) / 3, 
            1
        );

        return [
            'qc_metrics' => [
                'completed' => $qcMetrics['completed'],
                'required' => $qcMetrics['required'],
                'rate' => $qcMetrics['compliance_rate']
            ],
            'maintenance_metrics' => [
                'completed_am' => $maintenanceMetrics['completed_am'],
                'completed_pm' => $maintenanceMetrics['completed_pm'],
                'required' => $maintenanceMetrics['required_total'],
                'rate' => $maintenanceMetrics['compliance_rate']
            ],
            'production_metrics' => [
                'total' => $productionMetrics['total_runs'],
                'rate' => $productionMetrics['achievement_rate'],
                'problems' => $productionMetrics['problem_frequency']
            ],
            'performance_rate' => $performanceRate
        ];
    }

    public function render()
    {
        $query = User::query()
            ->with(['department', 'productions'])
            ->when($this->search, function($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            })
            ->when($this->selectedDepartment, function($q) {
                $q->where('department_id', $this->selectedDepartment);
            })
            ->when($this->selectedStatus, function($q) {
                if ($this->selectedStatus === 'active') {
                    $q->whereHas('productions', function($query) {
                        $query->where('status', 'running')
                              ->orWhere('status', 'pending');
                    });
                } else if ($this->selectedStatus === 'inactive') {
                    $q->whereDoesntHave('productions', function($query) {
                        $query->where('status', 'running')
                              ->orWhere('status', 'pending');
                    });
                }
            });
    
        if ($this->sortField) {
            $query->orderBy($this->sortField, $this->sortDirection);
        }
    
        $karyawan = $query->paginate(10);
        
        return view('livewire.manajerial.karyawan-report', [
            'karyawan' => $karyawan,
            'departments' => Department::all()
        ]);
    }

    private function getFilteredKaryawan()
    {
        return User::query()
            ->with(['department', 'productions'])
            ->when($this->search, function($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            })
            ->when($this->selectedDepartment, function($q) {
                $q->where('department_id', $this->selectedDepartment);
            })
            ->when($this->selectedStatus, function($q) {
                if ($this->selectedStatus === 'active') {
                    $q->whereHas('productions', function($query) {
                        $query->where('status', 'running')
                              ->orWhere('status', 'pending');
                    });
                } else if ($this->selectedStatus === 'inactive') {
                    $q->whereDoesntHave('productions', function($query) {
                        $query->where('status', 'running')
                              ->orWhere('status', 'pending');
                    });
                }
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->get();
    }

    public function previewPdf()
    {
        $karyawan = $this->getFilteredKaryawan()->map(function ($user) {
            $metrics = $this->calculateMetrics($user);
            return [
                'name' => $user->name,
                'department' => $user->department->name ?? '-',
                'qc_completed' => $metrics['qc_metrics']['completed'],
                'qc_required' => $metrics['qc_metrics']['required'],
                'qc_rate' => $metrics['qc_metrics']['rate'],
                'maintenance_am' => $metrics['maintenance_metrics']['completed_am'],
                'maintenance_pm' => $metrics['maintenance_metrics']['completed_pm'],
                'maintenance_rate' => $metrics['maintenance_metrics']['rate'],
                'production_total' => $metrics['production_metrics']['total'],
                'production_rate' => $metrics['production_metrics']['rate'],
                'performance_rate' => $metrics['performance_rate']
            ];
        });
    
        $pdf = Pdf::loadView('pdf.karyawan-report-list', [
            'karyawan' => $karyawan,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo
        ])
        ->setPaper('a4', 'landscape')
        ->setOptions([
            'defaultFont' => 'sans-serif',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true
        ]);
    
        return response($pdf->output())
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="laporan-karyawan.pdf"');
    }

    public function exportToPdf()
    {
        setlocale(LC_ALL, 'id_ID.UTF-8');
        
        $karyawan = $this->getFilteredKaryawan()->map(function ($user) {
            $metrics = $this->calculateMetrics($user);
            return [
                'name' => htmlspecialchars($user->name, ENT_QUOTES, 'UTF-8'),
                'department' => htmlspecialchars($user->department->name ?? '-', ENT_QUOTES, 'UTF-8'),
                'qc_completed' => (string)$metrics['qc_metrics']['completed'],
                'qc_required' => (string)$metrics['qc_metrics']['required'],
                'qc_rate' => (string)$metrics['qc_metrics']['rate'],
                'maintenance_am' => (string)$metrics['maintenance_metrics']['completed_am'],
                'maintenance_pm' => (string)$metrics['maintenance_metrics']['completed_pm'],
                'maintenance_rate' => (string)$metrics['maintenance_metrics']['rate'],
                'production_total' => (string)$metrics['production_metrics']['total'],
                'production_rate' => (string)$metrics['production_metrics']['rate'],
                'performance_rate' => (string)$metrics['performance_rate']
            ];
        })->toArray();

        $pdf = Pdf::loadView('pdf.karyawan-report-list', [
            'karyawan' => $karyawan,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo
        ])
        ->setPaper('a4', 'landscape')
        ->setOption('isHtml5ParserEnabled', true)
        ->setOption('isPhpEnabled', true)
        ->setOption('defaultFont', 'DejaVu Sans');

        return response($pdf->output())
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="preview-karyawan-report.pdf"');
    }
}