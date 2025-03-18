<?php

namespace App\Livewire\Manajerial\Sop;

use Livewire\Component;
use App\Models\Sop;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;

class SopApproval extends Component
{
    public $sops;
    public $showModal = false;
    public $rejection_reason;

    public function boot()
{
    $pendingCount = Sop::where('approval_status', 'pending')->count();
    view()->share('pendingCount', $pendingCount);
}

    public function mount()
    {
        // Get pending SOPs
        $this->sops = Sop::where('approval_status', 'pending')
                        ->with(['creator', 'machine', 'product'])
                        ->latest('submitted_at')
                        ->get();
    }

    public function render()
    {
        return view('livewire.manajerial.sop.sop-approval');
    }

    public function approve($id)
    {
        $sop = Sop::findOrFail($id);
        $sop->update([
            'approval_status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now()
        ]);

        session()->flash('success', 'SOP berhasil disetujui');
    }

    public function showRejectModal($id)
    {
        $this->sops = Sop::findOrFail($id);
        $this->showModal = true;
    }

    public function reject()
    {
        $this->validate([
            'rejection_reason' => 'required|min:10'
        ]);

        $this->sop->update([
            'approval_status' => 'rejected',
            'rejection_reason' => $this->rejection_reason,
            'rejected_by' => Auth::id(),
            'rejected_at' => now()
        ]);

        $this->showModal = false;
        $this->reset(['rejection_reason']);
        session()->flash('success', 'SOP telah ditolak');
    }
}