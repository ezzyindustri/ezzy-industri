<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Step Name</label>
        <input type="text" class="form-control" wire:model="judul">
        @error('judul') <span class="text-danger">{{ $message }}</span> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Step Order</label>
        <input type="number" class="form-control" wire:model="urutan">
        @error('urutan') <span class="text-danger">{{ $message }}</span> @enderror
    </div>

    <div class="col-12">
        <label class="form-label">Description</label>
        <textarea class="form-control" wire:model="deskripsi" rows="3"></textarea>
        @error('deskripsi') <span class="text-danger">{{ $message }}</span> @enderror
    </div>

    <div class="col-12">
        <label class="form-label">Image (Optional)</label>
        <input type="file" class="form-control" wire:model.live="gambar" accept="image/*">
        
        <div wire:loading wire:target="gambar">
            <div class="spinner-border spinner-border-sm text-primary mt-2" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <small class="text-muted ms-2">Uploading image...</small>
        </div>

        <div wire:loading wire:target="store,update">
            <div class="spinner-border spinner-border-sm text-primary mt-2" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <small class="text-muted ms-2">Saving data...</small>
        </div>

        @error('gambar') <span class="text-danger">{{ $message }}</span> @enderror
        
        @if($gambar && !$errors->has('gambar'))
            <div class="mt-2 position-relative">
                <img src="{{ $gambar->temporaryUrl() }}" class="img-thumbnail" style="max-height: 200px">
                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" 
                        wire:click="$set('gambar', null)">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        @endif
    </div>

    <div class="col-12">
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-secondary" wire:click="closeModal" 
                    wire:loading.attr="disabled" wire:target="store,update,gambar">
                Cancel
            </button>
            <button type="submit" class="btn btn-primary" 
                    wire:loading.attr="disabled" wire:target="store,update,gambar">
                <span wire:loading.remove wire:target="store,update">
                    {{ $isEditing ? 'Update' : 'Save' }}
                </span>
                <span wire:loading wire:target="store,update">
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    {{ $isEditing ? 'Updating...' : 'Saving...' }}
                </span>
            </button>
        </div>
    </div>
</div>