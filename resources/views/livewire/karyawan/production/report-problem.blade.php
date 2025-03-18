<div>
    <!-- Ganti dari reportProblemModal menjadi problemModal -->
    <div class="modal fade" id="problemModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Laporkan Masalah Produksi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form wire:submit="save">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Tipe Masalah</label>
                            <select class="form-select" wire:model="problemType" required>
                                <option value="">Pilih Tipe Masalah</option>
                                <option value="mesin">Masalah Mesin</option>
                                <option value="material">Masalah Material</option>
                                <option value="operator">Masalah Kualitas</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                            @error('problemType') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Catatan</label>
                            <textarea class="form-control" wire:model="notes" rows="3" required></textarea>
                            @error('notes') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Foto Dokumentasi</label>
                            <input type="file" class="form-control" wire:model="photo" accept="image/*">
                            <div wire:loading wire:target="photo" class="text-primary mt-1">
                                <small><i class="bi bi-arrow-repeat spinner"></i> Mengupload foto...</small>
                            </div>
                            @error('photo') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="save">Simpan</span>
                            <span wire:loading wire:target="save">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
