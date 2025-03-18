<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Parameter Name</label>
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

    <div class="col-md-6">
        <label class="form-label">Measurement Type</label>
        <select class="form-select" wire:model.live="measurement_type">
            <option value="">Select Type</option>
            <option value="length">Length</option>
            <option value="diameter">Diameter</option>
            <option value="weight">Weight</option>
            <option value="temperature">Temperature</option>
            <option value="pressure">Pressure</option>
            <option value="angle">Angle</option>
            <option value="time">Time</option>
            <option value="other">Other</option>
        </select>
        @error('measurement_type') <span class="text-danger">{{ $message }}</span> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Measurement Unit</label>
        <select class="form-select" wire:model="measurement_unit">
            <option value="">Select Unit</option>
            @if($measurement_type === 'length')
                <option value="mm">Millimeter (mm)</option>
                <option value="cm">Centimeter (cm)</option>
                <option value="m">Meter (m)</option>
            @elseif($measurement_type === 'diameter')
                <option value="mm">Millimeter (mm)</option>
                <option value="cm">Centimeter (cm)</option>
            @elseif($measurement_type === 'weight')
                <option value="g">Gram (g)</option>
                <option value="kg">Kilogram (kg)</option>
            @elseif($measurement_type === 'temperature')
                <option value="°C">Celsius (°C)</option>
                <option value="°F">Fahrenheit (°F)</option>
            @elseif($measurement_type === 'pressure')
                <option value="Bar">Bar</option>
                <option value="PSI">PSI</option>
            @elseif($measurement_type === 'angle')
                <option value="degree">Degree (°)</option>
            @elseif($measurement_type === 'time')
                <option value="s">Second (s)</option>
                <option value="min">Minute (min)</option>
                <option value="hour">Hour</option>
            @elseif($measurement_type === 'other')
                <option value="unit">Unit</option>
            @endif
        </select>
        @error('measurement_unit') <span class="text-danger">{{ $message }}</span> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Standard Value</label>
        <input type="text" class="form-control" wire:model="nilai_standar">
        @error('nilai_standar') <span class="text-danger">{{ $message }}</span> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Min Tolerance</label>
        <input type="text" class="form-control" wire:model="toleransi_min">
        @error('toleransi_min') <span class="text-danger">{{ $message }}</span> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Max Tolerance</label>
        <input type="text" class="form-control" wire:model="toleransi_max">
        @error('toleransi_max') <span class="text-danger">{{ $message }}</span> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Check Interval</label>
        <div class="input-group">
            <input type="number" class="form-control" wire:model="interval_value" placeholder="Value">
            <select class="form-select" wire:model="interval_unit">
                <option value="">Select Unit</option>
                <option value="pcs">Pieces</option>
                <option value="set">Set</option>
                <option value="box">Box</option>
                <option value="batch">Batch</option>
                <option value="hour">Hour</option>
                <option value="shift">Shift</option>
            </select>
        </div>
        @error('interval_value') <span class="text-danger">{{ $message }}</span> @enderror
        @error('interval_unit') <span class="text-danger">{{ $message }}</span> @enderror
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
</div>