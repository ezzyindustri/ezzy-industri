<?php

namespace App\Livewire\Karyawan\Production;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Production;
use App\Models\ProductionProblem;
use App\Models\OeeRecord;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;

class ReportProblem extends Component
{
    use WithFileUploads;

    public $productionId;
    public $problemType;
    public $notes;
    public $photo;

    #[On('openProblemModal')] 
    public function openProblemModal($productionId)
    {
        $this->productionId = $productionId;
        $this->dispatch('show-problem-modal');
    }


    public function save()
    {
        $this->validate([
            'problemType' => 'required',
            'notes' => 'required',
            'photo' => 'nullable|image|max:2048'
        ]);

        $imagePath = null;
        if ($this->photo) {
            $imagePath = $this->photo->store('problems', 'public');
        }

        ProductionProblem::create([
            'production_id' => $this->productionId,
            'problem_type' => $this->problemType,
            'notes' => $this->notes,
            'image_path' => $imagePath,
            'status' => 'pending',
            'reported_at' => now()
        ]);

        // Update production status
        Production::find($this->productionId)->update(['status' => 'problem']);

        // Update OEE Record secara real-time
        try {
            Log::info('Updating OEE record after problem reported', [
                'production_id' => $this->productionId,
                'problem_type' => $this->problemType
            ]);
            
            // Panggil metode updateFromProduction di model OeeRecord
            OeeRecord::updateFromProduction($this->productionId);
        } catch (\Exception $e) {
            Log::error('Error updating OEE record after problem reported: ' . $e->getMessage(), [
                'production_id' => $this->productionId
            ]);
        }

        $this->dispatch('closeModal'); // Ganti ke event yang sama dengan script JS
        $this->reset(['problemType', 'notes', 'photo']);
    
        $this->dispatch('refresh-production-status');
        
        $this->dispatch('show-toast', [
            'type' => 'success',
            'message' => 'Problem berhasil dilaporkan dan menunggu approval'
        ]);
    }

    public function render()
    {
        return view('livewire.karyawan.production.report-problem');
    }
}