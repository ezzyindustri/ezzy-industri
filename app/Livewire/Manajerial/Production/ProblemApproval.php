<?php

namespace App\Livewire\Manajerial\Production;

use Livewire\Component;
use App\Models\ProductionProblem;

class ProblemApproval extends Component
{
    public function render()
    {
        return view('livewire.manajerial.production.problem-approval', [
            'problems' => ProductionProblem::with(['production', 'production.machine'])  // tambahkan relasi machine
                ->orderBy('reported_at', 'desc')
                ->get()
        ]);
    }

    public function approve($problemId)
    {
        $problem = ProductionProblem::find($problemId);
        if ($problem) {
            $problem->status = 'approved';
            $problem->approved_at = now();
            $problem->save();
            
            $this->dispatch('problem-count-updated');
        }
    }

    public function reject($problemId)
    {
        $problem = ProductionProblem::find($problemId);
        if ($problem) {
            $problem->status = 'rejected';
            $problem->resolved_at = now();
            $problem->save();
            
            $problem->production->update(['status' => 'running']);
            $this->dispatch('problem-count-updated');
        }
    }
}