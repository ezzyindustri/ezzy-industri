@push('styles')
    <link href="{{ asset('assets/css/custom/pages/production-status.css') }}" rel="stylesheet">
@endpush
<div>
    <div class="pagetitle">
        <h1 class="mb-3">Status Produksi</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('karyawan.dashboard') }}">Beranda</a></li>
                <li class="breadcrumb-item active">Status Produksi</li>
            </ol>
        </nav>
    </div>

    <section class="section" wire:poll.5s>
        @if(!$activeProduction)
            <div class="text-center">
                <img src="{{ asset('assets/img/not-found.svg') }}" alt="No Production" class="img-fluid mb-3" style="max-width: 300px">
                <h4>Belum Ada Produksi Aktif</h4>
                <p class="text-muted">Silakan mulai produksi baru</p>
                <a href="{{ route('production.start') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Mulai Produksi Baru
                </a>
            </div>
        @else
            <div class="row">
                <!-- Informasi Produksi Column -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Informasi Produksi</h5>
                            <div class="d-flex flex-column gap-2">
                                <div class="d-flex justify-content-between">
                                    <strong>Mesin:</strong>
                                    <span>{{ $activeProduction->machine }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <strong>Produk:</strong>
                                    <span>{{ $activeProduction->product ?? '-' }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <strong>Shift:</strong>
                                    <span>{{ $activeProduction->shift->name ?? '-' }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <strong>Status:</strong>    
                                    <span class="badge bg-{{ 
                                        $activeProduction->status === 'running' ? 'success' : 
                                        ($activeProduction->status === 'problem' ? 'danger' : 
                                        ($activeProduction->status === 'paused' ? 'warning' : 'primary')) 
                                    }}">
                                        {{ ucfirst($activeProduction->status) }}
                                        @if($activeProduction->status === 'problem')
                                            @php
                                                $problem = $activeProduction->problems()->latest()->first();
                                            @endphp
                                            @if($problem && $problem->status === 'pending')
                                                <small>(Menunggu Approval)</small>
                                            @endif
                                        @endif
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <strong>Mulai:</strong>
                                    <span>{{ $activeProduction->start_time->setTimezone('Asia/Jakarta')->format('d M Y H:i') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Control and History Column -->
                <div class="col-lg-8">
                    <!-- Production Control Card -->
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Kontrol Produksi</h5>
                            <div class="text-center">
                                <div class="btn-group" role="group">
                                    @if ($activeProduction->status === 'running')
                                        <button class="btn btn-warning" wire:click="$dispatch('openDowntimeModal')" wire:loading.attr="disabled">
                                            <i class="bi bi-pause-circle me-1"></i> Record Downtime
                                        </button>
                                        <button class="btn btn-danger" wire:click="$dispatch('openProblemModal', { productionId: {{ $activeProduction->id }} })" wire:loading.attr="disabled">
                                            <i class="bi bi-exclamation-triangle me-1"></i> Problem
                                        </button>
                                        <a href="{{ route('production.quality-check', ['productionId' => $activeProduction->id]) }}" 
                                           class="btn btn-info">
                                            <i class="bi bi-clipboard-check me-1"></i> Quality Check
                                        </a>
                                        <a href="{{ route('production.finish', ['productionId' => $activeProduction->id]) }}" 
                                           class="btn btn-success">
                                            <i class="bi bi-check-circle me-1"></i> Selesai
                                        </a>
                                    @endif

                                    @if ($activeProduction->status === 'paused')
                                        <button class="btn btn-primary" wire:click="resumeProduction" wire:loading.attr="disabled">
                                            <i class="bi bi-play-circle me-1"></i> Resume
                                        </button>
                                    @endif

                                    @if ($activeProduction->status === 'problem')
                                        @php
                                            $problem = $activeProduction->problems()->latest()->first();
                                        @endphp
                                        @if($problem && $problem->status === 'approved')
                                            <button class="btn btn-success" wire:click="resolveProblem" wire:loading.attr="disabled">
                                                <i class="bi bi-check-circle me-1"></i> Resolve Problem
                                            </button>
                                        @endif
                                    @endif

                                    @if ($activeProduction->status === 'finished')
                                        <a href="{{ route('production.report', $activeProduction->id) }}" 
                                           class="btn btn-info">
                                            <i class="bi bi-file-pdf me-1"></i> Download Report
                                        </a>
                                    @endif
                                </div>
                            </div>
                            <!-- Quality Check Progress -->
                            <div class="mt-4">
                                <h6>Progress Pengisian Quality Check</h6>
                                <div class="progress mt-3">
                                    <div class="progress-bar progress-bar-striped bg-primary progress-bar-animated" 
                                         role="progressbar" 
                                         @style("width: {$checkProgress}%")
                                         aria-valuenow="{{ $checkProgress }}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">{{ $checkProgress }}%</div>
                                </div>
                                <small class="text-muted mt-2 d-block">
                                    Terisi {{ $completedChecks }} dari {{ $totalChecksNeeded }} check yang diperlukan
                                    (Interval: setiap {{ $intervalCheck }} set)
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Quality Check History Card -->
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title">Riwayat Quality Check</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Waktu</th>
                                            <th>Sample</th>
                                            <th>Parameter</th>
                                            <th>Nilai</th>
                                            <th>Status</th>
                                            <th>Operator</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($activeProduction->qualityChecks()->with(['details', 'user'])->latest()->get() as $check)
                                            @foreach($check->details as $detail)
                                                <tr>
                                                    @if($loop->first)
                                                        <td rowspan="{{ $check->details->count() }}">
                                                            {{ $check->check_time->format('d/m/Y H:i') }}
                                                        </td>
                                                        <td rowspan="{{ $check->details->count() }}">
                                                            {{ $check->sample_size }}
                                                        </td>
                                                    @endif
                                                    <td>{{ $detail->parameter }}</td>
                                                    <td>
                                                        {{ number_format(floatval(str_replace(',', '.', $detail->measured_value)), 
                                                            floatval($detail->measured_value) < 0.1 ? 4 : (floatval($detail->measured_value) < 1 ? 2 : 0), 
                                                            ',', '') 
                                                        }}
                                                        <small class="text-muted d-block">
                                                            ({{ number_format(floatval($detail->tolerance_min), 4, ',', '') }} - 
                                                             {{ number_format(floatval($detail->tolerance_max), 4, ',', '') }})
                                                        </small>
                                                    </td>
                                                    <td>
                                                        @php
                                                            $value = floatval(str_replace(',', '.', $detail->measured_value));
                                                            $min = floatval(str_replace(',', '.', $detail->tolerance_min));
                                                            $max = floatval(str_replace(',', '.', $detail->tolerance_max));
                                                            $epsilon = $value < 0.1 ? 0.00001 : 0.001;
                                                            $status = ($value >= ($min - $epsilon) && $value <= ($max + $epsilon)) ? 'ok' : 'ng';
                                                        @endphp
                                                        <span class="badge bg-{{ $status === 'ok' ? 'success' : 'danger' }}">
                                                            {{ strtoupper($status) }}
                                                        </span>
                                                    </td>
                                                    @if($loop->first)
                                                        <td rowspan="{{ $check->details->count() }}">
                                                            {{ $check->user->name }}
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center">Belum ada data quality check</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- SOP Check Results Card -->
                    @if($activeProduction && $activeProduction->productionSopChecks && $activeProduction->productionSopChecks->count() > 0)
                        <div class="card mt-4">
                            <div class="card-body">
                                <h5 class="card-title">Hasil Pemeriksaan SOP</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Langkah</th>
                                                <th>Nilai Standar</th>
                                                <th>Hasil Pengukuran</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($activeProduction->productionSopChecks as $check)
                                                <tr>
                                                    <td>{{ $check->step->judul }}</td>
                                                    <td>
                                                        {{ $check->step->nilai_standar }}
                                                        <small class="d-block text-muted">
                                                            Toleransi: {{ $check->step->toleransi_min }} - {{ $check->step->toleransi_max }}
                                                        </small>
                                                    </td>
                                                    <td>{{ $check->hasil_pengukuran }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ $check->status === 'ok' ? 'success' : 'danger' }}">
                                                            {{ strtoupper($check->status) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </section>

    <!-- Di bagian bawah file -->
    <livewire:karyawan.production.report-problem />

    <!-- Single Downtime Modal Instance -->
    <div class="modal fade" id="downtimeModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Record Downtime</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit="saveDowntime">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Downtime Type</label>
                            <select class="form-select" wire:model="reason" required>
                                <option value="">Select Type</option>
                                <option value="break">Break Time</option>
                                <option value="meeting">Meeting</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="setup">Machine Setup</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" wire:model="notes" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Start Downtime</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @script
    <script>
        document.addEventListener('livewire:initialized', () => {
            const downtimeModal = new bootstrap.Modal('#downtimeModal');
            const problemModal = new bootstrap.Modal('#problemModal');
            
            Livewire.on('openDowntimeModal', () => {
                downtimeModal.show();
            });
            
            Livewire.on('show-problem-modal', () => {
                problemModal.show();
            });

            Livewire.on('closeModal', () => {
                downtimeModal.hide();
                problemModal.hide();
            });

            // Handle modal close with escape key or clicking outside
            document.querySelectorAll('.modal').forEach(modal => {
                modal.addEventListener('hidden.bs.modal', () => {
                    Livewire.dispatch('closeModal');
                });
            });
        });
    </script>
    @endscript
</div>