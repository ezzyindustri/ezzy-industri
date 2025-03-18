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
    // Basic properties
    public $production;
    public $productionId;
    public $sampleSize = 1;
    public $notes;
    public $parameters = [];
    public $sop;
    public $measurements = [];
    
    // NG-related properties
    public $hasNG = false;
    public $showNGModal = false;
    public $ngData = [
        'count' => 0,
        'type' => '',
        'notes' => '',
        'step_id' => null
    ];

    // Ganti protected $listeners dengan #[On] attribute untuk Livewire v3
    #[On('showNGForm')]
    public function showNGModal()
    {   
        $this->showNGModal = true;
        $this->dispatchBrowserEvent('show-ng-modal');
    }

    public function cancelNG()
    {
    $this->showNGModal = false;
    $this->dispatch('closeNGModal');
    }
    
    public function mount($productionId)
    {
        $this->productionId = $productionId;
        $this->production = Production::with(['product', 'shift'])->findOrFail($productionId);
        $this->loadSop();
    }

    public function checkMeasurement($stepId)
    {
        if (isset($this->measurements[$stepId])) {
            $step = $this->sop->steps->where('id', $stepId)->first();
            
            $measurement = trim($this->measurements[$stepId]);
            if ($measurement === '' || !is_numeric($measurement)) {
                return;
            }
            $value = floatval(str_replace(',', '.', $measurement));
            $min = floatval(str_replace(',', '.', $step->toleransi_min));
            $max = floatval(str_replace(',', '.', $step->toleransi_max));
    
            $epsilon = $value < 0.1 ? 0.00001 : 0.001;
            
            $this->hasNG = ($value < ($min - $epsilon)) || ($value > ($max + $epsilon));
            
            if ($this->hasNG) {
                $this->ngData['step_id'] = $stepId;
                $this->showNGModal = true;
                $this->dispatch('show-alert', 'Ukuran di luar toleransi!'); // Tambah event SweetAlert
            }
        }
    }
    

    public function render()
    {
        return view('livewire.karyawan.quality-check.quality-check-form');
    }

    #[On('openNgModal')] 
    public function openNgModal()
    {
        $this->showNGModal = true;
    }

    public function validateMeasurements()
    {
        Log::info('Starting measurement validation');
        $hasNG = false;
        
        foreach ($this->measurements as $stepId => $value) {
            $this->validate([
                'measurements.*' => 'required|numeric',
            ]);
    
            foreach ($this->sop->steps as $step) {
                $measurement = trim($this->measurements[$step->id] ?? '');
                if ($measurement === '' || !is_numeric($measurement)) {
                    continue;
                }
    
                $value = floatval(str_replace(',', '.', $measurement));
                $min = floatval(str_replace(',', '.', $step->toleransi_min));
                $max = floatval(str_replace(',', '.', $step->toleransi_max));
    
                if ($value < $min || $value > $max) {
                    $this->hasNG = true;
                    $this->ngData['step_id'] = $step->id;
                    Log::info('NG detected', ['step_id' => $step->id]);
                    // Gunakan nama event yang konsisten
                    $this->dispatch('ng-detected');
                    return;
                }
            }
        }
        
        // Jika tidak ada NG, simpan data dan redirect ke halaman status produksi
        if (!$this->hasNG) {
            $this->saveCheck();
            return redirect()->route('production.status');
        }
    }


public function saveNGData()
{
    $this->validate([
        'ngData.count' => 'required|integer|min:1',
        'ngData.type' => 'required|string|in:dimensional,surface,material',
        'ngData.notes' => 'required|string|max:255'
    ]);

    Log::info('Saving NG data', [
        'count' => $this->ngData['count'],
        'type' => $this->ngData['type']
    ]);

    try {
        DB::beginTransaction();
        
        // Simpan data NG ke tabel quality_checks
        $qualityCheck = QualityCheck::create([
            'production_id' => $this->productionId,
            'user_id' => Auth::id(),
            'status' => 'ng',
            'sample_size' => $this->sampleSize,
            'defect_count' => $this->ngData['count'],
            'defect_type' => $this->ngData['type'],
            'defect_notes' => $this->ngData['notes'],
            'check_time' => now()
        ]);
        
        // Simpan semua detail pengukuran ke tabel quality_check_details
        foreach ($this->measurements as $stepId => $value) {
            $step = $this->sop->steps->where('id', $stepId)->first();
            
            if ($step && is_numeric(str_replace(',', '.', $value))) {
                // Gunakan fungsi helper untuk konversi nilai
                $measuredValue = $this->convertToDecimal($value);
                $minValue = $this->convertToDecimal($step->toleransi_min);
                $maxValue = $this->convertToDecimal($step->toleransi_max);
                $standardValue = $this->convertToDecimal($step->nilai_standar);
                
                $epsilon = $measuredValue < 0.1 ? 0.00001 : 0.001;
                $status = ($measuredValue >= ($minValue - $epsilon) && $measuredValue <= ($maxValue + $epsilon)) ? 'ok' : 'ng';
                
                QualityCheckDetail::create([
                    'quality_check_id' => $qualityCheck->id,
                    'parameter' => $step->judul,
                    'standard_value' => $standardValue,
                    'measured_value' => $measuredValue,
                    'tolerance_min' => $minValue,
                    'tolerance_max' => $maxValue,
                    'status' => $status
                ]);
            }
        }
        
        DB::commit();
        $this->showNGModal = false;
        $this->dispatch('closeNGModal');
        session()->flash('success', 'Data NG berhasil disimpan!');
        
        // Redirect ke halaman production status
        return redirect()->route('production.status');
        
    } catch (\Exception $e) {
        DB::rollback();
        Log::error('Error saving NG data: ' . $e->getMessage());
        session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
    }
}


   
    public function loadSop()
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
            'defect_count' => $this->ngData['count'],
            'defect_type' => $this->ngData['type'],
            'defect_notes' => $this->ngData['notes']
        ]);

        foreach ($this->measurements as $stepId => $value) {
            $step = $this->sop->steps->where('id', $stepId)->first();
            
            if ($step) {
                // Perbaikan konversi nilai desimal
                $measuredValue = $this->convertToDecimal($value);
                $minValue = $this->convertToDecimal($step->toleransi_min);
                $maxValue = $this->convertToDecimal($step->toleransi_max);
                $standardValue = $this->convertToDecimal($step->nilai_standar);
                
                $epsilon = $measuredValue < 0.1 ? 0.00001 : 0.001;
                $status = ($measuredValue >= ($minValue - $epsilon) && $measuredValue <= ($maxValue + $epsilon)) ? 'ok' : 'ng';

                QualityCheckDetail::create([
                    'quality_check_id' => $qualityCheck->id,
                    'parameter' => $step->judul,
                    'standard_value' => $standardValue,
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

// Tambahkan fungsi helper untuk konversi nilai desimal dengan presisi yang tepat
private function convertToDecimal($value)
{
    // Hapus semua spasi
    $value = trim($value);
    
    // Ganti koma dengan titik untuk format desimal
    $value = str_replace(',', '.', $value);
    
    // Pastikan nilai adalah numerik
    if (is_numeric($value)) {
        // Konversi ke string untuk mempertahankan presisi
        $floatValue = (float) $value;
        
        // Hitung jumlah digit setelah desimal di nilai asli
        $decimalPlaces = 0;
        if (strpos($value, '.') !== false) {
            $decimalPlaces = strlen(substr(strrchr($value, '.'), 1));
        }
        
        // Gunakan format dengan presisi yang sesuai
        return number_format($floatValue, $decimalPlaces, '.', '');
    }
    
    return 0; // Default jika tidak valid
}
}