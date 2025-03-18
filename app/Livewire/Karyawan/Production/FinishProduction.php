<?php

namespace App\Livewire\Karyawan\Production;

use Livewire\Component;
use App\Models\Production;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; 


class FinishProduction extends Component
{
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