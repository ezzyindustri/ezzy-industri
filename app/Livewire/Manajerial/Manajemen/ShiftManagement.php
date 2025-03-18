<?php

namespace App\Livewire\Manajerial\Manajemen;

use Livewire\Component;
use App\Models\Shift;

class ShiftManagement extends Component
{
    public $shifts;
    public $showModal = false;
    public $editMode = false;
    public $shiftId;
    
    public $form = [
        'name' => '',
        'start_time' => '',
        'end_time' => '',
        'status' => 'active'
    ];

    public function mount()
    {
        $this->refreshShifts();
    }

    public function refreshShifts()
    {
        $this->shifts = Shift::all();
    }

    public function createShift()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function editShift($id)
    {
        $this->shiftId = $id;
        $this->editMode = true;
        $shift = Shift::find($id);
        $this->form = $shift->only(['name', 'start_time', 'end_time', 'status']);
        $this->showModal = true;
    }

    public function saveShift()
    {
    $this->validate([
        'form.name' => 'required',
        'form.start_time' => 'required',
        'form.end_time' => 'required',
        'form.status' => 'required|in:active,inactive'
    ]);

        if ($this->editMode) {
            Shift::find($this->shiftId)->update($this->form);
        } else {
            Shift::create($this->form);
        }

        $this->closeModal();
        $this->refreshShifts();
        session()->flash('message', $this->editMode ? 'Shift updated.' : 'Shift created.');
    }

    public function deleteShift($id)
    {
        Shift::find($id)->delete();
        $this->refreshShifts();
        session()->flash('message', 'Shift deleted.');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->form = [
            'name' => '',
            'start_time' => '',
            'end_time' => '',
            'status' => 'active'
        ];
        $this->shiftId = null;
    }

    public function render()
    {
        return view('livewire.manajerial.manajemen.shift-management');
    }
}