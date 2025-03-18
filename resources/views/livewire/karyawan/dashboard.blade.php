@push('styles')
<link href="{{ asset('assets/css/custom/pages/dashboard.css') }}" rel="stylesheet">
@endpush
<div class="dashboard">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="fw-bold">Dashboard Karyawan</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item active">Overview</li>
                    </ol>
                </nav>
            </div>
            <div class="date-display">
                <i class="bi bi-calendar3"></i>
                <span>{{ now()->format('d F Y') }}</span>
            </div>
        </div>
    </div>

  <!-- Production Overview -->
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card bg-gradient-primary">
            <div class="card-body">
                <div class="stat-content">
                    <div class="icon-box">
                        <i class="bi bi-box-seam-fill"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ $todayProduction }}</h3>
                        <p class="stat-label">Total Produksi</p>
                        <span class="stat-text">Hari ini</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card stat-card bg-gradient-danger">
            <div class="card-body">
                <div class="stat-content">
                    <div class="icon-box">
                        <i class="bi bi-x-octagon-fill"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ $todayDefects }}</h3>
                        <p class="stat-label">Total Defect</p>
                        <span class="stat-text">Hari ini</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card stat-card bg-gradient-warning">
            <div class="card-body">
                <div class="stat-content">
                    <div class="icon-box">
                        <i class="bi bi-stopwatch-fill"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ $totalDowntime }} <small>menit</small></h3>
                        <p class="stat-label">Total Downtime</p>
                        <span class="stat-text">Hari ini</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card stat-card bg-gradient-success">
            <div class="card-body">
                <div class="stat-content">
                    <div class="icon-box">
                        <i class="bi bi-gear-fill"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ $activeProduction ? ucfirst($activeProduction->status) : 'Tidak Aktif' }}</h3>
                        <p class="stat-label">Status Produksi</p>
                        <span class="stat-text">Saat ini</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- Recent Activity -->
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card activity-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title">
                            <i class="bi bi-clock-history me-2"></i>
                            Downtime Terakhir
                        </h5>
                        <span class="badge rounded-pill bg-light text-dark">
                            Hari Ini
                        </span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>Alasan</th>
                                    <th>Durasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentDowntimes as $downtime)
                                <tr>
                                    <td>{{ $downtime->start_time->format('H:i:s') }}</td>
                                    <td>{{ $downtime->reason }}</td>
                                    <td>{{ $downtime->duration_minutes ?? 'Ongoing' }} menit</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card activity-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Masalah Terakhir
                        </h5>
                        <span class="badge rounded-pill bg-light text-dark">
                            Hari Ini
                        </span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>Deskripsi</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentProblems as $problem)
                                <tr>
                                    <td>{{ $problem->reported_at->format('H:i:s') }}</td>
                                    <td>{{ $problem->notes }}</td>
                                    <td>
                                        <span class="badge bg-{{ $problem->status === 'resolved' ? 'success' : ($problem->status === 'approved' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($problem->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>