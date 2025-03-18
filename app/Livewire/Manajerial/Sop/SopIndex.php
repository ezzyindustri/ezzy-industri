<?php

namespace App\Livewire\Manajerial\Sop;

use Livewire\Component;
use App\Models\Sop;
use App\Models\Machine;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;


class SopIndex extends Component
{
    public $nama;
    public $kategori;
    public $deskripsi;
    public $versi;
    public $machine_id;
    public $editId;
    public $isEditing = false;
    public $product;
    public $product_id;
    public $no_sop;

    protected $rules = [
        'no_sop' => 'required|unique:sops,no_sop',
        'nama' => 'required|min:3',
        'kategori' => 'required',
        'deskripsi' => 'nullable',
        'versi' => 'required',
        'machine_id' => 'required_if:kategori,produksi,safety|exists:machines,id|nullable',
        'product_id' => 'required_if:kategori,quality|exists:products,id|nullable'
    ];

    public function render()
    {
        return view('livewire.manajerial.sop.sop-index', [
            'sops' => Sop::with(['machine', 'product', 'creator', 'approver'])->latest()->get(),
            'machines' => Machine::orderBy('name')->get(),
            'products' => Product::orderBy('name')->get()
        ]);
    }

    public function store()
    {
        $this->validate();

        Sop::create([
            'no_sop' => strtoupper($this->no_sop), // Tambahkan ini dan ubah ke uppercase
            'nama' => $this->nama,
            'kategori' => $this->kategori,
            'deskripsi' => $this->deskripsi,
            'versi' => $this->versi,
            'machine_id' => $this->machine_id,
            'product_id' => $this->product_id,
            'created_by' => Auth::id(),
            'created_date' => now(),
            'approval_status' => 'draft'
        ]);

        $this->reset(['no_sop', 'nama', 'kategori', 'deskripsi', 'versi', 'machine_id', 'product_id']);
        session()->flash('success', 'SOP berhasil ditambahkan');
    }

    public function edit($id)
    {
        $sop = Sop::find($id);
        $this->editId = $id;
        $this->no_sop = $sop->no_sop;  // Tambahkan ini
        $this->nama = $sop->nama;
        $this->kategori = $sop->kategori;
        $this->deskripsi = $sop->deskripsi;
        $this->versi = $sop->versi;
        $this->machine_id = $sop->machine_id;
        $this->product_id = $sop->product_id;
        $this->isEditing = true;
    }

    public function update()
    {
        $this->rules['no_sop'] = 'required|unique:sops,no_sop,' . $this->editId;
        
        $this->validate();

        $sop = Sop::find($this->editId);
        
        $sop->update([
            'no_sop' => strtoupper($this->no_sop),
            'nama' => $this->nama,
            'kategori' => $this->kategori,
            'deskripsi' => $this->deskripsi,
            'versi' => $this->versi,
            'machine_id' => $this->machine_id,
            'product_id' => $this->product_id
        ]);

        $this->reset(['no_sop', 'nama', 'kategori', 'deskripsi', 'versi', 'machine_id', 'product_id', 'editId', 'isEditing']);
        session()->flash('success', 'SOP berhasil diperbarui');
    }

    public $sopToDelete;

    public function confirmDelete($id)
    {
        $this->sopToDelete = $id;
        $this->dispatch('show-delete-confirmation');
    }

    public function delete()
    {
        $sop = Sop::find($this->sopToDelete);
        
        if ($sop->approval_status !== 'draft') {
            session()->flash('error', 'Hanya SOP dengan status draft yang dapat dihapus');
            return;
        }

        $sop->delete();
        $this->dispatch('deleted');
    }

    protected $listeners = ['deleteConfirmed' => 'delete'];
}