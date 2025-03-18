<div class="modal fade" id="ngFormModal" tabindex="-1" wire:ignore.self>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Input Data NG</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form wire:submit="saveNGData">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Jumlah NG</label>
                        <input type="number" class="form-control" wire:model="ngData.count" required min="1">
                        @error('ngData.count') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jenis NG</label>
                        <select class="form-select" wire:model="ngData.type" required>
                            <option value="">Pilih Jenis NG</option>
                            <option value="dimensional">Dimensional</option>
                            <option value="surface">Surface</option>
                            <option value="material">Material</option>
                        </select>
                        @error('ngData.type') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea class="form-control" wire:model="ngData.notes" rows="3" required></textarea>
                        @error('ngData.notes') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="cancelNG">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>