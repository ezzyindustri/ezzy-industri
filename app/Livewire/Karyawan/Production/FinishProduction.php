<?php

namespace App\Livewire\Karyawan\Production;

use Livewire\Component;
use App\Models\Production;
use App\Models\OeeRecord;
use App\Models\Machine;
use App\Traits\OeeAlertTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; 

class FinishProduction extends Component
{
    use OeeAlertTrait;
    
    public $production;
    public $totalProduction = 0;
    public $defectCount = 0;
    public $defectType; 
    public $notes;

    public function mount($productionId)
    {
        $this->production = Production::where('id', $productionId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if (!in_array($this->production->status, ['running', 'paused'])) {
            return redirect()->route('production.status');
        }
    }

    public function finish()
    {
        $this->validate([
            'totalProduction' => 'required|integer|min:1',
            'defectCount' => 'required|integer|min:0|lte:totalProduction',
            'defectType' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:255'
        ]);

        try {
            $this->production->update([
                'status' => 'finished',
                'end_time' => now(),
                'total_production' => $this->totalProduction,
                'defect_count' => $this->defectCount,
                'defect_type' => $this->defectType,
                'notes' => $this->notes
            ]);

            // Setelah produksi selesai, hitung OEE dan kirim notifikasi jika perlu
            $productionId = $this->production->id;
            
            Log::info('Production finished, checking OEE for notifications', [
                'production_id' => $productionId,
                'machine_id' => $this->production->machine_id
            ]);
            
            // Update OEE record secara real-time
            try {
                // Panggil metode updateFromProduction di model OeeRecord
                $oeeRecord = OeeRecord::updateFromProduction($productionId);
                
                if ($oeeRecord) {
                    Log::info('OEE Record updated successfully', [
                        'production_id' => $productionId,
                        'oee_score' => $oeeRecord->oee_score
                    ]);
                    
                    // Kirim notifikasi jika OEE di bawah target
                    $machine = Machine::find($this->production->machine_id);
                    if ($machine && $oeeRecord->oee_score < $machine->oee_target) {
                        Log::info('OEE below target, sending notification', [
                            'oee_score' => $oeeRecord->oee_score,
                            'target' => $machine->oee_target
                        ]);
                        
                        // Gunakan trait OeeAlertTrait
                        $this->checkAndSendOeeAlert($machine, $oeeRecord->oee_score, $productionId);
                    }
                } else {
                    Log::warning('Failed to update OEE record', [
                        'production_id' => $productionId
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Error updating OEE record: ' . $e->getMessage(), [
                    'production_id' => $productionId
                ]);
            }

            Log::info('Mencoba menampilkan modal');
            
            // Coba gunakan event yang berbeda
            $this->dispatch('finish-success');
            $this->dispatch('show-finish-modal');
            
            Log::info('Event telah di-dispatch');
            
        } catch (\Exception $e) {
            Log::error('Error saat finish production: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan saat menyelesaikan produksi.');
        }
    }

    public function redirectToStart()
    {
        return redirect()->route('production.start');
    }

    public function render()
    {
        return view('livewire.karyawan.production.finish-production');
    }
}