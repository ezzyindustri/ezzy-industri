<?php

namespace App\Livewire\Components;

use Livewire\Component;
use App\Models\ProductionProblem;

class ProblemCount extends Component
{
    public function render()
    {
        return view('livewire.components.problem-count', [
            'count' => ProductionProblem::where('status', 'waiting')->count()
        ]);
    }

    protected $listeners = ['problem-count-updated' => '$refresh'];
}