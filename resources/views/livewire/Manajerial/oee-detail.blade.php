@push('styles')
    <link href="{{ asset('assets/css/custom/pages/oee-detail.css') }}" rel="stylesheet">
@endpush
<div wire:poll.{{ $refreshInterval }}="refreshData">
    <div class="pagetitle">
        <h1>Detail OEE {{ $machine->name }}</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('manajerial.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('manajerial.oee-dashboard') }}">OEE Dashboard</a></li>
                <li class="breadcrumb-item active">Detail OEE</li>
            </ol>
        </nav>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Periode</label>
                    <select class="form-select" wire:model.live="selectedPeriod">
                        <option value="daily">Harian</option>
                        <option value="weekly">Mingguan</option>
                        <option value="monthly">Bulanan</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <a href="{{ route('manajerial.oee.detail.pdf', [
                        'machineId' => $machine->id,
                        'period' => $selectedPeriod
                    ]) }}" 
                    class="btn btn-danger" 
                    target="_blank">
                        <i class="bi bi-file-pdf"></i> Download PDF
                    </a>
                </div>
                <div class="col-md-4 text-end">
                    <div class="alert alert-info p-2 mb-0">
                        <small><i class="bi bi-clock"></i> Terakhir diperbarui: {{ $lastUpdated }}</small>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Availability Rate</h5>
                            <h2 class="text-center {{ $averageAvailability < 60 ? 'text-danger' : ($averageAvailability < 85 ? 'text-warning' : 'text-success') }}">
                                {{ $averageAvailability }}%
                            </h2>
                            <small>Operating Time / Planned Production Time</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Performance Rate</h5>
                            <h2 class="text-center {{ $averagePerformance < 60 ? 'text-danger' : ($averagePerformance < 85 ? 'text-warning' : 'text-success') }}">
                                {{ $averagePerformance }}%
                            </h2>
                            <small>(Total Output × Ideal Cycle Time) / Operating Time</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Quality Rate</h5>
                            <h2 class="text-center {{ $averageQuality < 60 ? 'text-danger' : ($averageQuality < 85 ? 'text-warning' : 'text-success') }}">
                                {{ $averageQuality }}%
                            </h2>
                            <small>Good Output / Total Output</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">OEE Score</h5>
                            <h2 class="text-center {{ $oeeScore < 60 ? 'text-danger' : ($oeeScore < 85 ? 'text-warning' : 'text-success') }}">
                                {{ $oeeScore }}%
                            </h2>
                            <small>Availability × Performance × Quality</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Grafik OEE</h5>
                            <div id="oeeChart" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Detail Availability</h5>
                            <table class="table table-sm">
                                <tr>
                                    <td>Planned Production Time</td>
                                    <td>{{ $plannedProductionTime }} menit</td>
                                </tr>
                                <tr>
                                    <td>Downtime (Problems)</td>
                                    <td>{{ $downtimeProblems }} menit</td>
                                </tr>
                                <tr>
                                    <td>Downtime (Maintenance)</td>
                                    <td>{{ $downtimeMaintenance }} menit</td>
                                </tr>
                                <tr>
                                    <td>Total Downtime</td>
                                    <td>{{ $totalDowntime }} menit</td>
                                </tr>
                                <tr>
                                    <td>Operating Time</td>
                                    <td>{{ $operatingTime }} menit</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Detail Performance & Quality</h5>
                            <table class="table table-sm">
                            <tr>
                                    <td>Ideal Cycle Time</td>
                                    <td>{{ $idealCycleTime }} menit</td>
                                </tr>
                                <tr>
                                    <td>Total Output</td>
                                    <td>{{ $totalOutput }} unit</td>
                                </tr>
                                <tr>
                                    <td>Defect Count</td>
                                    <td>{{ $defectCount }} unit</td>
                                </tr>
                                <tr>
                                    <td>Good Output</td>
                                    <td>{{ $goodOutput }} unit</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @script
    <script>
        let oeeChart;
        
        document.addEventListener('livewire:initialized', () => {
            // Inisialisasi chart dengan data awal
            const chartData = JSON.parse('{{ json_encode($chartData) }}'.replace(/&quot;/g, '"'));
            if (chartData) {
                oeeChart = initChart(chartData);
            }
            
            // Update chart ketika data berubah
            Livewire.on('chartDataUpdated', (data) => {
                updateChart(oeeChart, data);
            });
        });
    </script>
    @endscript

    <!-- Load ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <!-- Load OEE Chart JS -->
    <script src="{{ asset('assets/js/oee-chart.js') }}"></script>
</div>