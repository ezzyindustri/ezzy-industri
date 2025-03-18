<?php

namespace App\Livewire\Karyawan\QualityCheck;

use Livewire\Component;
use App\Models\Production;
use App\Models\QualityCheck;
use App\Models\QualityCheckDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; 
use App\Models\Sop;
use App\Models\ProductionSopCheck;
use Livewire\Attributes\On;


class QualityCheckForm extends Component
{
    public $production;
    public $productionId;
    public $sampleSize = 1;
    public $notes;
    public $parameters = [];
    public $sop;
   
    

    // Add new properties
    public $measurements = []; // Add this property for measurements
    public $hasNG = false;
    public $defectCount;
    public $defectType;
    public $defectNotes;
    public $showDefectModal = false;  // Add this if not exists

    public function checkMeasurement($stepId)
    {
        if (isset($this->measurements[$stepId])) {
            $step = $this->sop->steps->where('id', $stepId)->first();
            
            // Convert all values consistently
            $value = floatval(str_replace(',', '.', $this->measurements[$stepId]));
            $min = floatval(str_replace(',', '.', $step->toleransi_min));
            $max = floatval(str_replace(',', '.', $step->toleransi_max));
            
            // Use higher epsilon for smaller values
            $epsilon = $value < 0.1 ? 0.00001 : 0.001;
            
            // Debug log
            Log::info('Quality Check Values', [
                'measured' => $value,
                'min' => $min,
                'max' => $max,
                'epsilon' => $epsilon
            ]);
            
            $this->hasNG = ($value < ($min - $epsilon)) || ($value > ($max + $epsilon));
        }
    }


    public function render()
    {
        return view('livewire.karyawan.quality-check.quality-check-form');
    }

    public function mount($productionId)
    {
        $this->productionId = $productionId;
        $this->production = Production::with(['product', 'shift'])->findOrFail($productionId);
        
        // Initialize defect properties
        $this->defectCount = 0;
        $this->defectType = '';
        $this->defectNotes = '';
        
        $this->loadSop();
    }

    public function validateMeasurements()
    {
        Log::info('Starting measurement validation');
        
        $this->validate([
            'measurements.*' => 'required|numeric',
        ]);
    
        $this->hasNG = false;
        foreach ($this->sop->steps as $step) {
            $value = floatval(str_replace(',', '.', $this->measurements[$step->id] ?? 0));
            $min = floatval(str_replace(',', '.', $step->toleransi_min));
            $max = floatval(str_replace(',', '.', $step->toleransi_max));
            
            Log::info('Checking measurement', [
                'step_id' => $step->id,
                'value' => $value,
                'min' => $min,
                'max' => $max
            ]);
            
            if ($value < $min || $value > $max) {
                $this->hasNG = true;
                Log::info('NG detected', ['step_id' => $step->id]);
                break;
            }
        }
    
        if ($this->hasNG) {
            Log::info('Showing NG confirmation');
            $this->dispatch('show-ng-confirmation');
        } else {
            Log::info('Proceeding to save check');
            $this->saveCheck();
        }
    }

    // Tambahkan attribute Livewire untuk menangkap event
    #[On('showDefectModal')] 
    public function showDefectModal()
    {
        $this->dispatch('showDefectModal');
    }

    public function saveDefectData()
    {
        $this->validate([
            'defectCount' => 'required|integer|min:1',
            'defectType' => 'required|string',
            'defectNotes' => 'required|string'
        ]);
    
        // Hide modal before saving
        $this->dispatch('hideDefectModal');
        
        $this->saveCheck();
    }

    public function saveCheck()
    {
        try {
            DB::beginTransaction();
    
            $qualityCheck = QualityCheck::create([
                'production_id' => $this->productionId,
                'user_id' => Auth::id(),
                'sample_size' => $this->sampleSize,
                'notes' => $this->notes,
                'check_time' => now(),
                'status' => $this->hasNG ? 'ng' : 'ok',
                'defect_count' => $this->defectCount,
                'defect_type' => $this->defectType,
                'defect_notes' => $this->defectNotes
            ]);
    
            // Perbaiki bagian ini
            foreach ($this->measurements as $stepId => $value) {
                $step = $this->sop->steps->where('id', $stepId)->first();
                
                if ($step) {
                    // Convert values consistently
                    $measuredValue = floatval(str_replace(',', '.', $value));
                    $minValue = floatval(str_replace(',', '.', $step->toleransi_min));
                    $maxValue = floatval(str_replace(',', '.', $step->toleransi_max));
                    
                    // Use same epsilon logic as in form
                    $epsilon = $measuredValue < 0.1 ? 0.00001 : 0.001;
                    $status = ($measuredValue >= ($minValue - $epsilon) && $measuredValue <= ($maxValue + $epsilon)) ? 'ok' : 'ng';
    
                    QualityCheckDetail::create([
                        'quality_check_id' => $qualityCheck->id,
                        'parameter' => $step->judul,
                        'standard_value' => floatval(str_replace(',', '.', $step->nilai_standar)),
                        'measured_value' => $measuredValue,
                        'tolerance_min' => $minValue,
                        'tolerance_max' => $maxValue,
                        'status' => $status
                    ]);
                }
            }
    
            DB::commit();
            session()->flash('success', 'Data pemeriksaan kualitas berhasil disimpan');
            return redirect()->route('production.status');
    
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Quality Check Error: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    protected function loadSop()
    {
        $qualitySop = Sop::where('product_id', $this->production->product_id)
                         ->where('kategori', 'quality')
                         ->where('is_active', true)
                         ->where('approval_status', 'approved')
                         ->with(['steps' => function($query) {
                             $query->orderBy('urutan', 'asc');
                         }])
                         ->first();

        if ($qualitySop && $qualitySop->steps) {
            $this->sop = $qualitySop;
            // Map steps untuk parameter quality check
            $this->parameters = $qualitySop->steps
                ->map(function($step) {
                    return [
                        'name' => $step->judul,
                        'description' => $step->deskripsi,
                        'value' => null,
                        'standard' => $step->nilai_standar,
                        'min' => $step->toleransi_min,
                        'max' => $step->toleransi_max,
                        'unit' => $step->measurement_unit,
                        'type' => $step->measurement_type
                    ];
                })->toArray();
        }
    }
}