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
use App\Models\OeeRecord;

class QualityCheckForm extends Component
{
    // Basic properties
    public $production;
    public $productionId;
    public $stepId; // Add this property declaration
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
    
    public function mount($productionId = null, $stepId = null)
    {
        $this->productionId = $productionId;
        $this->stepId = $stepId; // Pastikan stepId diinisialisasi di sini
        $this->production = Production::with(['product', 'shift'])->findOrFail($productionId);
        $this->loadSop();
    }

    public function checkMeasurement($stepId)
    {
        if (isset($this->measurements[$stepId])) {
            $step = $this->sop->steps->where('id', $stepId)->first();
            
            $measurement = trim($this->measurements[$stepId]);
            if ($measurement === '') {
                return;
            }
            
            // Ganti koma dengan titik untuk validasi numerik
            $numericValue = str_replace(',', '.', $measurement);
            if (!is_numeric($numericValue)) {
                return;
            }
            
            $value = floatval($numericValue);
            $min = floatval(str_replace(',', '.', $step->toleransi_min));
            $max = floatval(str_replace(',', '.', $step->toleransi_max));

            $epsilon = $value < 0.1 ? 0.00001 : 0.001;
            
            if (($value < ($min - $epsilon)) || ($value > ($max + $epsilon))) {
                $this->hasNG = true;
                $this->ngData['step_id'] = $stepId;
                // Tidak perlu menampilkan modal atau alert di sini
                // Hanya tandai bahwa ada nilai NG
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
        
        // Modifikasi validasi untuk menerima format angka dengan koma
        $this->validate([
            'measurements.*' => [
                'required',
                function ($attribute, $value, $fail) {
                    // Ganti koma dengan titik untuk validasi numerik
                    $numericValue = str_replace(',', '.', $value);
                    if (!is_numeric($numericValue)) {
                        $fail('Nilai harus berupa angka.');
                    }
                },
            ],
        ]);
        
        // Periksa apakah ada nilai di luar toleransi
        $hasNG = false;
        foreach ($this->measurements as $stepId => $measurement) {
            $step = $this->sop->steps->where('id', $stepId)->first();
            if (!$step) continue;
            
            $value = floatval(str_replace(',', '.', $measurement));
            $min = floatval(str_replace(',', '.', $step->toleransi_min));
            $max = floatval(str_replace(',', '.', $step->toleransi_max));
            
            $epsilon = $value < 0.1 ? 0.00001 : 0.001;
            
            if (($value < ($min - $epsilon)) || ($value > ($max + $epsilon))) {
                $hasNG = true;
                $this->ngData['step_id'] = $stepId;
                break;
            }
        }
        
        if ($hasNG) {
            $this->hasNG = true;
            // Hanya kirim event untuk menampilkan konfirmasi
            $this->dispatch('show-ng-modal');
            return;
        }
        
        // Jika tidak ada NG, simpan data
        $this->saveCheck();
    }

/**
 * Menyimpan data NG (Not Good/defect)
 */
public function saveNGData()
{
    Log::info('Saving NG data', $this->ngData);
    
    $this->validate([
        'ngData.count' => 'required|numeric|min:1',
        'ngData.type' => 'required|string',
        'ngData.notes' => 'required|string',
    ]);

    try {
        // Gunakan data dari array ngData
        Log::info('Saving NG data', [
            'count' => $this->ngData['count'],
            'type' => $this->ngData['type'],
            'notes' => $this->ngData['notes'],
            'step_id' => $this->ngData['step_id']
        ]);
        
        // Simpan data NG ke database
        $qualityCheck = QualityCheck::create([
            'production_id' => $this->productionId,
            'step_id' => $this->ngData['step_id'],
            'defect_count' => $this->ngData['count'],
            'defect_type' => $this->ngData['type'],
            'notes' => $this->ngData['notes'],
            'user_id' => Auth::id(),
            'check_time' => now(),
            'status' => 'ng',
            'sample_size' => $this->sampleSize ?? 1, // Add sample_size field with default value 1
        ]);
        
        // Segera update OEE record setelah menyimpan data NG
        Log::info('Updating OEE record after quality check', [
            'production_id' => $this->productionId,
            'has_ng' => true
        ]);
        
        // Update OEE record
        $oeeRecord = OeeRecord::updateFromProduction($this->productionId);
        
    } catch (\Exception $e) {
        Log::error('Error saving NG data: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);
        session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        return;
    }

    $this->showNGModal = false;
    
    // Simpan data quality check dengan status NG
    $this->saveCheck();
    
    // Redirect ke halaman production status
    return redirect()->route('production.status')->with('success', 'Data quality check berhasil disimpan');
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

                // Di method saveCheck dan saveNGData, ubah bagian pembuatan QualityCheckDetail
                QualityCheckDetail::create([
                    'quality_check_id' => $qualityCheck->id,
                    'parameter' => $step->judul,
                    'standard_value' => $this->convertToDecimal($step->nilai_standar),
                    'measured_value' => $this->convertToDecimal($value),
                    'tolerance_min' => $this->convertToDecimal($step->toleransi_min),
                    'tolerance_max' => $this->convertToDecimal($step->toleransi_max),
                    'status' => $status
                ]);
            }
        }

        DB::commit();
        
        // Update OEE Record secara real-time
        try {
            Log::info('Updating OEE record after quality check', [
                'production_id' => $this->productionId,
                'has_ng' => $this->hasNG
            ]);
            
            // Panggil metode updateFromProduction di model OeeRecord
            OeeRecord::updateFromProduction($this->productionId);
        } catch (\Exception $e) {
            Log::error('Error updating OEE record after quality check: ' . $e->getMessage(), [
                'production_id' => $this->productionId
            ]);
        }
        
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
        // Kembalikan nilai sebagai string untuk mempertahankan presisi
        return $value;
    }
    
    return '0'; // Default jika tidak valid
}
}