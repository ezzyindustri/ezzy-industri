<?php

namespace App\Livewire\Manajerial\Manajemen;

use Livewire\Component;
use App\Models\Machine;

class MachineManagement extends Component
{
    public $machines;
    public $showModal = false;
    public $showDeleteModal = false;
    public $editMode = false;
    public $machineId;
    public $machineToDelete;
    
    public $form = [
        'code' => '',
        'name' => '',
        'type' => '',
        'description' => '',
        'location' => '',
        'status' => 'active',
        'oee_target' => 85.00,
        'alert_enabled' => true,
        'alert_email' => '',
        'alert_phone' => '' // Tambahkan field alert_phone
    ];

    public function mount()
    {
        $this->refreshMachines();
    }

    public function refreshMachines()
    {
        $this->machines = Machine::all();
    }

    public function createMachine()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function editMachine($id)
    {
        $this->machineId = $id;
        $this->editMode = true;
        $machine = Machine::find($id);
        $this->form = $machine->only([
            'code', 'name', 'type', 'description', 
            'location', 'status', 'oee_target', 
            'alert_enabled', 'alert_email', 'alert_phone' // Tambahkan alert_phone
        ]);
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function confirmDelete($id)
    {
        $this->machineToDelete = $id;
        $this->showDeleteModal = true;
    }
    public function cancelDelete()
    {
        $this->machineToDelete = null;
        $this->showDeleteModal = false;
    }

    public function saveMachine()
    {
        $this->validate([
            'form.code' => 'required|unique:machines,code,' . $this->machineId,
            'form.name' => 'required',
            'form.type' => 'required',
            'form.status' => 'required|in:active,inactive',
            'form.oee_target' => 'required|numeric|min:0|max:100',
            'form.alert_enabled' => 'boolean',
            'form.alert_email' => 'nullable|required_if:form.alert_enabled,true|email',
            'form.alert_phone' => 'nullable|regex:/^[0-9]{10,15}$/' // Validasi nomor telepon
        ]);

        if ($this->editMode) {
            Machine::find($this->machineId)->update($this->form);
            session()->flash('message', 'Mesin berhasil diupdate.');
        } else {
            Machine::create($this->form);
            session()->flash('message', 'Mesin berhasil ditambahkan.');
        }

        $this->closeModal();
        $this->refreshMachines();
    }

    private function resetForm()
    {
        $this->form = [
            'code' => '',
            'name' => '',
            'type' => '',
            'description' => '',
            'location' => '',
            'status' => 'active',
            'oee_target' => 85.00,
            'alert_enabled' => true,
            'alert_email' => '',
            'alert_phone' => '' // Reset alert_phone
        ];
        $this->machineId = null;
    }

    public function deleteMachine()
    {
        if ($this->machineToDelete) {
            Machine::find($this->machineToDelete)->delete();
            $this->refreshMachines();
            session()->flash('message', 'Mesin berhasil dihapus.');
            $this->showDeleteModal = false;
            $this->machineToDelete = null;
        }
    }

    public function render()
    {
        return view('livewire.manajerial.manajemen.machine-management');
    }
}
