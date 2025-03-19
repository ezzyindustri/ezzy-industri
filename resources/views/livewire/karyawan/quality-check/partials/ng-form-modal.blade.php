<div wire:ignore.self class="modal fade" id="ngFormModal" tabindex="-1" aria-labelledby="ngFormModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ngFormModalLabel">Form NG</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form wire:submit="saveNGData">
                    <div class="mb-3">
                        <label class="form-label">Jumlah NG</label>
                        <input type="number" class="form-control" wire:model="ngData.count" min="1">
                        @error('ngData.count') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jenis NG</label>
                        <input type="text" class="form-control" wire:model="ngData.type">
                        @error('ngData.type') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea class="form-control" wire:model="ngData.notes"></textarea>
                        @error('ngData.notes') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="cancelNG">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>