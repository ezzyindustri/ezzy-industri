<div>
    <div class="pagetitle">
        <h1>Laporan Kinerja Karyawan</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('manajerial.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active">Laporan Kinerja</li>
            </ol>
        </nav>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title">Filter</h5>
                <div class="btn-group">
                    <button class="btn btn-success me-2" wire:click="exportExcel">
                        <i class="bi bi-file-excel"></i> Export Excel
                    </button>
                    <a href="{{ route('manajerial.karyawan.report.pdf', [
                        'dateFrom' => $dateFrom,
                        'dateTo' => $dateTo,
                        'department' => $selectedDepartment,
                        'status' => $selectedStatus
                    ]) }}" 
                    class="btn btn-danger" 
                    target="_blank">
                        <i class="bi bi-file-pdf"></i> Download PDF
                    </a>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">Cari Karyawan</label>
                    <input type="text" class="form-control" wire:model.live="search" placeholder="Nama karyawan...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Dari Tanggal</label>
                    <input type="date" class="form-control" 
                           wire:model.live="dateFrom" 
                           value="{{ $dateFrom }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sampai Tanggal</label>
                    <input type="date" class="form-control" 
                           wire:model.live="dateTo"
                           value="{{ $dateTo }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Departemen</label>
                    <select class="form-select" wire:model.live="selectedDepartment">
                        <option value="">Semua Departemen</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" wire:model.live="selectedStatus">
                        <option value="">Semua Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <!-- Ganti input date dengan select periode -->
                <div class="col-md-3">
                    <label class="form-label">Periode</label>
                    <select class="form-select" wire:model.live="selectedPeriod">
                        <option value="0">Hari Ini</option>
                        <option value="7">7 Hari Terakhir</option>
                        <option value="14">14 Hari Terakhir</option>
                        <option value="30">30 Hari Terakhir</option>
                        <option value="90">3 Bulan Terakhir</option>
                        <option value="180">6 Bulan Terakhir</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th wire:click="sortBy('name')" style="cursor: pointer">
                                Nama Karyawan 
                                @if($sortField === 'name')
                                    <i class="bi bi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </th>
                            <th>Departemen</th>
                            <th>Quality Check</th>
                            <th>PM/AM Check</th>
                            <th>Produksi</th>
                            <th>Performance</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($karyawan as $k)
                        <tr>
                            <td>{{ $k->name }}</td>
                            <td>{{ $k->department->name ?? '-' }}</td>
                            <!-- Quality Check Column -->
                            <td>
                                <div>Completed: {{ $k->qc_metrics['completed'] ?? 0 }}/{{ $k->qc_metrics['required'] ?? 0 }}</div>
                                <div>Rate: {{ $k->qc_metrics['compliance_rate'] ?? 0 }}%</div>
                                <!-- Quality Check progress bar -->
                                <div class="progress mt-1" style="height: 5px;">
                                    <div class="progress-bar progress-bar-striped bg-success progress-bar-animated" 
                                         role="progressbar" 
                                         @style([
                                             'width' => ($k->qc_metrics['compliance_rate'] ?? 0) . '%'
                                         ])
                                         aria-valuenow="{{ $k->qc_metrics['compliance_rate'] ?? 0 }}"
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                    </div>
                                </div>
                            </td>
                            <!-- Maintenance Check Column -->
                            <td>
                                Rate: {{ $k->maintenance_metrics['rate'] ?? 0 }}%
                                <!-- Maintenance Check progress bar -->
                                <div class="progress mt-1" style="height: 5px;">
                                    <div class="progress-bar progress-bar-striped bg-info progress-bar-animated" 
                                         role="progressbar" 
                                         @style([
                                             'width' => ($k->maintenance_metrics['rate'] ?? 0) . '%'
                                         ])
                                         aria-valuenow="{{ $k->maintenance_metrics['rate'] ?? 0 }}"
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                    </div>
                                </div>
                            </td>
                            
                            <!-- Production Column -->
                            <td>
                                <div>Total: {{ $k->production_metrics['total_runs'] ?? 0 }}</div>
                                <div>Achievement: {{ $k->production_metrics['achievement_rate'] ?? 0 }}%</div>
                                <div>Problems: {{ $k->production_metrics['problem_frequency'] ?? 0 }}</div>
                                <!-- Production progress bar -->
                                <div class="progress mt-1" style="height: 5px;">
                                    <div class="progress-bar progress-bar-striped bg-primary progress-bar-animated" 
                                         role="progressbar" 
                                         @style([
                                             'width' => ($k->production_metrics['achievement_rate'] ?? 0) . '%'
                                         ])
                                         aria-valuenow="{{ $k->production_metrics['achievement_rate'] ?? 0 }}"
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                    </div>
                                </div>
                            </td>
                            <td>
                                @php
                                    $qcRate = $k->qc_metrics['compliance_rate'] ?? 0;
                                    $maintenanceRate = $k->maintenance_metrics['rate'] ?? 0;
                                    $productionRate = $k->production_metrics['achievement_rate'] ?? 0;
                                    $performanceRate = ($qcRate + $maintenanceRate + $productionRate) / 3;
                                @endphp
                                @if($performanceRate >= 90)
                                    <span class="badge bg-success">Excellent</span>
                                @elseif($performanceRate >= 75)
                                    <span class="badge bg-info">Good</span>
                                @elseif($performanceRate >= 60)
                                    <span class="badge bg-warning">Fair</span>
                                @else
                                    <span class="badge bg-danger">Poor</span>
                                @endif
                                {{ number_format($performanceRate, 1) }}%
                                <!-- Performance progress bar -->
                                <div class="progress mt-1" style="height: 5px;">
                                    <div class="progress-bar progress-bar-striped bg-primary progress-bar-animated" 
                                         role="progressbar" 
                                         @style([
                                             'width' => $performanceRate . '%'
                                         ])
                                         aria-valuenow="{{ $performanceRate }}"
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                    </div>
                                </div>
                            </td>
                            <td>    
                                <a href="{{ route('manajerial.karyawan.detail.pdf', [
                                    'userId' => $k->id,
                                    'dateFrom' => $dateFrom,
                                    'dateTo' => $dateTo
                                ]) }}" 
                                class="btn btn-sm btn-danger" 
                                target="_blank">
                                    <i class="bi bi-file-pdf"></i> Detail PDF
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">Tidak ada data</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($karyawan->hasPages())
                <div class="mt-3">
                    {{ $karyawan->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal for Detail View -->
    <div class="modal fade" id="detailModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Kinerja Karyawan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Quality Check Details</h6>
                            <!-- Add detailed QC metrics -->
                        </div>
                        <div class="col-md-6">
                            <h6>Maintenance Check Details</h6>
                            <!-- Add detailed maintenance metrics -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

