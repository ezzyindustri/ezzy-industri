<div>
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
            <button type="button" class="btn btn-info"  data-bs-toggle="modal" data-bs-target="#oeeGuideModal">
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
                        <a href="{{ route('manajerial.oee.detail', $machine['id']) }}" 
                           class="btn btn-sm btn-primary">
                            <i class="bi bi-graph-up"></i> Detail
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
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Pedoman Overall Equipment Effectiveness (OEE)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Pengenalan OEE -->
                    <div class="mb-4">
                        <h6 class="fw-bold">Apa itu OEE?</h6>
                        <p>Overall Equipment Effectiveness (OEE) adalah metrik standar untuk mengukur efektivitas manufaktur. OEE mengidentifikasi persentase waktu manufaktur yang benar-benar produktif dan digunakan untuk mengidentifikasi kerugian yang terjadi selama proses produksi.</p>
                    </div>

                    <!-- Komponen OEE -->
                    <div class="mb-4">
                        <h6 class="fw-bold">Komponen OEE</h6>
                        <p>OEE terdiri dari tiga komponen utama:</p>
                        
                        <div class="card mb-3">
                            <div class="card-body">
                                <h6 class="card-title">1. Availability (Ketersediaan)</h6>
                                <p class="mb-2">Mengukur total waktu mesin beroperasi dibandingkan dengan waktu yang direncanakan.</p>
                                <div class="bg-light p-2 rounded">
                                    <strong>Rumus:</strong> (Operating Time ÷ Planned Production Time) × 100%<br>
                                    <small class="text-muted">Operating Time = Planned Production Time - Total Downtime</small>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-3">
                            <div class="card-body">
                                <h6 class="card-title">2. Performance (Kinerja)</h6>
                                <p class="mb-2">Mengukur kecepatan aktual operasi dibandingkan dengan kecepatan idealnya.</p>
                                <div class="bg-light p-2 rounded">
                                    <strong>Rumus:</strong> ((Total Output × Ideal Cycle Time) ÷ Operating Time) × 100%
                                </div>
                            </div>
                        </div>

                        <div class="card mb-3">
                            <div class="card-body">
                                <h6 class="card-title">3. Quality (Kualitas)</h6>
                                <p class="mb-2">Mengukur jumlah produk yang memenuhi standar kualitas.</p>
                                <div class="bg-light p-2 rounded">
                                    <strong>Rumus:</strong> (Good Output ÷ Total Output) × 100%
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Perhitungan OEE -->
                    <div class="mb-4">
                        <h6 class="fw-bold">Perhitungan OEE</h6>
                        <div class="bg-light p-3 rounded">
                            <p class="mb-2">OEE = Availability × Performance × Quality</p>
                            <small class="text-muted">Nilai dinyatakan dalam persentase (%)</small>
                        </div>
                    </div>

                    <!-- Standar Nilai OEE -->
                    <div class="mb-4">
                        <h6 class="fw-bold">Standar Nilai OEE</h6>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <span class="badge bg-danger">< 60%</span>
                                <span class="ms-2">Performa buruk, perlu perbaikan segera</span>
                            </li>
                            <li class="mb-2">
                                <span class="badge bg-warning">60% - 85%</span>
                                <span class="ms-2">Performa cukup, masih ada ruang untuk improvement</span>
                            </li>
                            <li>
                                <span class="badge bg-success">> 85%</span>
                                <span class="ms-2">Performa baik, pertahankan dan tingkatkan</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Sumber Data -->
                    <div>
                        <h6 class="fw-bold">Sumber Data</h6>
                        <ul>
                            <li>Planned Production Time: Dari master data shift</li>
                            <li>Downtime: Dari catatan masalah dan pemeliharaan mesin</li>
                            <li>Ideal Cycle Time: Dari master data produk</li>
                            <li>Total Output: Dari catatan produksi</li>
                            <li>Defect Count: Dari catatan quality control</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</div>
