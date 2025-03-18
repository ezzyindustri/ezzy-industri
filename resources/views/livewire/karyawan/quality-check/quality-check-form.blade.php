@push('styles')
<link href="{{ asset('assets/css/custom/pages/quality-check.css') }}" rel="stylesheet">
@endpush
<div>
    <div class="pagetitle">
        <h1 class="mb-3">Pemeriksaan Kualitas</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('karyawan.dashboard') }}">Beranda</a></li>
                <li class="breadcrumb-item"><a href="{{ route('production.status') }}">Status Produksi</a></li>
                <li class="breadcrumb-item active">Pemeriksaan Kualitas</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">IN PROCESS INSPECTION REPORT</h5>
                        
                        <!-- Info Header -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <table class="table table-sm table-bordered">
                                    <tr>
                                        <th width="30%">Machine</th>
                                        <td>{{ $production->machine }}</td>
                                    </tr>
                                    <tr>
                                        <th>Product</th>
                                        <td>{{ $production->product }}</td>
                                    </tr>
                                    <tr>
                                        <th>Date/Shift</th>
                                        <td>{{ now()->format('d-m-Y') }} / {{ $production->shift->name ?? '-' }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm table-bordered">
                                    <tr>
                                        <th width="30%">SOP Number</th>
                                        <td>{{ $sop ? $sop->no_sop : 'No SOP Available' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Check Time</th>
                                        <td>{{ now()->format('H:i') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <form wire:submit.prevent="validateMeasurements">
                            <!-- Parameter Table -->
                            <div class="table-responsive mb-3">
                            @if($sop)
                                <table class="table table-bordered table-sm">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="5%">No.</th>
                                                <th width="15%">Parameter</th>
                                                <th width="20%">Description</th>
                                                <th width="15%">Standard</th>
                                                <th width="15%">Tolerance</th>
                                                <th width="15%">Measured Value</th>
                                                <th width="15%">Reference Image</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($sop->steps as $index => $step)
                                            <tr>
                                                <td class="text-center">{{ $step->urutan }}</td>
                                                <td>{{ $step->judul }}</td>
                                                <td>{{ $step->deskripsi }}</td>
                                                <td class="text-center">
                                                    <span class="measurement-value">
                                                        {{ $step->nilai_standar }}
                                                    </span>
                                                    <span class="measurement-unit">
                                                        @switch($step->measurement_unit)
                                                            @case('mm')
                                                                mm
                                                                @break
                                                            @case('cm')
                                                                cm
                                                                @break
                                                            @case('kg')
                                                                kg
                                                                @break
                                                            @case('°C')
                                                                °C
                                                                @break
                                                            @default
                                                                {{ $step->measurement_unit }}
                                                        @endswitch
                                                    </span>
                                                    <div class="measurement-type">
                                                        @switch($step->measurement_type)
                                                            @case('diameter')
                                                                <i class="bi bi-circle"></i> Ø
                                                                @break
                                                            @case('length')
                                                                <i class="bi bi-arrows-expand"></i>
                                                                @break
                                                            @case('weight')
                                                                <i class="bi bi-weight"></i>
                                                                @break
                                                            @case('temperature')
                                                                <i class="bi bi-thermometer-half"></i>
                                                                @break
                                                        @endswitch
                                                        {{ ucfirst($step->measurement_type) }}
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="tolerance-value">
                                                        @php
                                                            $standardValue = floatval(str_replace(',', '.', $step->nilai_standar ?? '0'));
                                                            $toleranceMax = floatval(str_replace(',', '.', $step->toleransi_max ?? '0'));
                                                            $toleranceRange = $toleranceMax - $standardValue;
                                                            
                                                            // Determine decimal places based on the value
                                                            $decimals = $standardValue < 0.1 ? 4 : ($standardValue < 1 ? 2 : 0);
                                                            
                                                            $formattedStandard = number_format($standardValue, $decimals, ',', '');
                                                            $formattedRange = number_format($toleranceRange, $decimals, ',', '');
                                                        @endphp
                                                        {{ $formattedStandard }} ± {{ $formattedRange }}
                                                        <span class="measurement-unit">{{ $step->measurement_unit }}</span>
                                                    </span>
                                                </td>
                                                <td>
                                                    @php
                                                        // Determine step precision based on standard value
                                                        $standardValue = floatval(str_replace(',', '.', $step->nilai_standar ?? '0'));
                                                        $inputStep = $standardValue < 0.1 ? '0.0001' : '0.01';
                                                    @endphp
                                                    <input type="number" 
                                                        class="form-control form-control-sm" 
                                                        wire:model.live="measurements.{{ $step->id }}"
                                                        wire:change="checkMeasurement({{ $step->id }})"
                                                        step="{{ $inputStep }}">
                                                    @error("measurements.{$step->id}") 
                                                        <span class="text-danger small">{{ $message }}</span>
                                                    @enderror
                                                </td>
                                                <td>
                                                    @if($step->gambar_path)
                                                        <div class="reference-image">
                                                            <img src="{{ asset('storage/' . $step->gambar_path) }}" 
                                                                 class="img-reference"
                                                                 alt="Reference Image"
                                                                 title="Click to zoom"
                                                                 onclick="window.open(this.src)">
                                                        </div>
                                                    @else
                                                        <span class="text-muted">No reference image</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="6" class="text-center">No quality parameters found</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                @else
                                    <div class="alert alert-warning">
                                        SOP Quality Check belum tersedia untuk produk ini. Silahkan hubungi supervisor.
                                    </div>
                                @endif
                            </div>

                            <!-- Notes -->
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="form-label">Notes & Action Taken</label>
                                    <textarea class="form-control" wire:model="notes" rows="2"></textarea>
                                    @error('notes') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="text-end">
                                <a href="{{ route('production.status') }}" class="btn btn-secondary me-2">
                                    <i class="bi bi-x-circle me-1"></i> Batal
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-1"></i> Simpan
                                </button>
                            </div>
                        </form>
                        <!-- Single Modal Defect Entry -->
                        <div class="modal fade" id="defectModal" tabindex="-1" wire:ignore.self>
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Input Data Defect</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form wire:submit.prevent="saveDefectData">
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Jumlah Defect</label>
                                                <input type="number" class="form-control" wire:model="defectCount" required min="1">
                                                @error('defectCount') <span class="text-danger">{{ $message }}</span> @enderror
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Jenis Defect</label>
                                                <select class="form-select" wire:model="defectType" required>
                                                    <option value="">Pilih Jenis Defect</option>
                                                    <option value="dimensional">Dimensional</option>
                                                    <option value="surface">Surface</option>
                                                    <option value="material">Material</option>
                                                </select>
                                                @error('defectType') <span class="text-danger">{{ $message }}</span> @enderror
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Alasan/Keterangan</label>
                                                <textarea class="form-control" wire:model="defectNotes" rows="3" required></textarea>
                                                @error('defectNotes') <span class="text-danger">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" wire:click="$dispatch('hideDefectModal')">Batal</button>
                                            <button type="submit" class="btn btn-primary">Simpan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        @push('scripts')
                        <script>
                        document.addEventListener('livewire:initialized', () => {
                            const defectModal = new bootstrap.Modal(document.getElementById('defectModal'));
                            
                            Livewire.on('showDefectModal', () => {
                                defectModal.show();
                            });
                            
                            Livewire.on('hideDefectModal', () => {
                                defectModal.hide();
                            });
                        });
                        </script>
                        @endpush
                    </div>
                </div>
            </div>
        </section>
    </div>
