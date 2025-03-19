<div wire:poll.{{ $refreshInterval }}="refreshData">
    <div class="pagetitle">
        <h1>OEE Dashboard</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('manajerial.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active">OEE Dashboard</li>
            </ol>
        </nav>
    </div>

    <!-- Tambahkan Button Petunjuk -->
    <div class="row mb-3">
        <div class="col-12">
            <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#oeeGuideModal">
                <i class="bi bi-info-circle"></i> Petunjuk/Pedoman OEE
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" class="form-control" wire:model.live="startDate">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Akhir</label>
                    <input type="date" class="form-control" wire:model.live="endDate">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Shift</label>
                    <select class="form-select" wire:model.live="selectedShift">
                        <option value="">Semua Shift</option>
                        @foreach($shifts as $shift)
                            <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <a href="{{ route('manajerial.oee.dashboard.pdf', [
                        'startDate' => $startDate,
                        'endDate' => $endDate,
                        'shift' => $selectedShift
                    ]) }}" 
                    class="btn btn-danger" 
                    target="_blank">
                        <i class="bi bi-file-pdf"></i> Download PDF
                    </a>
                </div>
            </div>

            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Data OEE diperbarui secara otomatis setiap 5 menit. Terakhir diperbarui: {{ now()->format('H:i:s') }}
            </div>

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Mesin</th>
                            <th>Availability</th>
                            <th>Performance</th>
                            <th>Quality</th>
                            <th>OEE Score</th>
                            <th>Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($machines as $machine)
                            <tr>
                                <td>{{ $machine['name'] }}</td>
                                
                                <td>
                                    <span class="badge {{ $machine['availability_rate'] > 0 
                                        ? ($machine['availability_rate'] < 60 ? 'bg-danger' 
                                        : ($machine['availability_rate'] < 85 ? 'bg-warning' : 'bg-success')) 
                                        : 'bg-danger' }} w-100 p-2">
                                        {{ $machine['availability_rate'] ?? 0 }}%
                                    </span>
                                </td>
                                
                                <td>
                                    <span class="badge {{ $machine['performance_rate'] > 0 
                                        ? ($machine['performance_rate'] < 60 ? 'bg-danger' 
                                        : ($machine['performance_rate'] < 85 ? 'bg-warning' : 'bg-success')) 
                                        : 'bg-danger' }} w-100 p-2">
                                        {{ $machine['performance_rate'] ?? 0 }}%
                                    </span>
                                </td>
                                
                                <td>
                                    <span class="badge {{ $machine['quality_rate'] > 0 
                                        ? ($machine['quality_rate'] < 60 ? 'bg-danger' 
                                        : ($machine['quality_rate'] < 85 ? 'bg-warning' : 'bg-success')) 
                                        : 'bg-danger' }} w-100 p-2">
                                        {{ $machine['quality_rate'] ?? 0 }}%
                                    </span>
                                </td>
                                
                                <td>
                                    <span class="badge {{ $machine['oee_score'] > 0 
                                        ? ($machine['oee_score'] < 60 ? 'bg-danger' 
                                        : ($machine['oee_score'] < 85 ? 'bg-warning' : 'bg-success')) 
                                        : 'bg-danger' }} w-100 p-2">
                                        {{ $machine['oee_score'] ?? 0 }}%
                                    </span>
                                </td>
                                
                                <td>
                                    <a href="{{ route('manajerial.oee-detail', $machine['id']) }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> Lihat Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Petunjuk OEE -->
    <div class="modal fade" id="oeeGuideModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Petunjuk & Pedoman OEE</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h5>Apa itu OEE?</h5>
                    <p>Overall Equipment Effectiveness (OEE) adalah metrik standar untuk mengukur efektivitas manufaktur. OEE mengidentifikasi persentase waktu produksi yang benar-benar produktif.</p>
                    
                    <h5>Komponen OEE:</h5>
                    <ul>
                        <li><strong>Availability (Ketersediaan)</strong>: Perbandingan waktu operasi aktual dengan waktu produksi yang direncanakan.</li>
                        <li><strong>Performance (Kinerja)</strong>: Perbandingan output aktual dengan output ideal berdasarkan waktu siklus.</li>
                        <li><strong>Quality (Kualitas)</strong>: Perbandingan produk baik dengan total produk yang diproduksi.</li>
                    </ul>
                    
                    <h5>Rumus Perhitungan:</h5>
                    <ul>
                        <li><strong>Availability</strong> = Operating Time / Planned Production Time</li>
                        <li><strong>Performance</strong> = (Total Output × Ideal Cycle Time) / Operating Time</li>
                        <li><strong>Quality</strong> = Good Output / Total Output</li>
                        <li><strong>OEE</strong> = Availability × Performance × Quality</li>
                    </ul>
                    
                    <h5>Standar Nilai OEE:</h5>
                    <ul>
                        <li><span class="badge bg-danger">Buruk: &lt; 60%</span></li>
                        <li><span class="badge bg-warning">Cukup: 60% - 85%</span></li>
                        <li><span class="badge bg-success">Baik: &gt; 85%</span></li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</div>