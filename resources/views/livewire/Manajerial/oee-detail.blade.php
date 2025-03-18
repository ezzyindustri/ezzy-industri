@push('styles')
    <link href="{{ asset('assets/css/custom/pages/oee-detail.css') }}" rel="stylesheet">
@endpush
<div>
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

            <div class="card mt-4">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">OEE Trend</h5>

                            <!-- Area Chart -->
                            <div id="oeeAreaChart" 
                                data-availability='{{ json_encode($chartData["availability"] ?? []) }}'
                                data-performance='{{ json_encode($chartData["performance"] ?? []) }}'
                                data-quality='{{ json_encode($chartData["quality"] ?? []) }}'
                                data-oee='{{ json_encode($chartData["oee"] ?? []) }}'
                                data-labels='{{ json_encode($chartData["labels"] ?? []) }}'
                            ></div>

                            <script>
                                document.addEventListener("DOMContentLoaded", () => {
                                    const chartElement = document.querySelector("#oeeAreaChart");
                                    const chartData = {
                                        availability: JSON.parse(chartElement.dataset.availability),
                                        performance: JSON.parse(chartElement.dataset.performance),
                                        quality: JSON.parse(chartElement.dataset.quality),
                                        oee: JSON.parse(chartElement.dataset.oee),
                                        labels: JSON.parse(chartElement.dataset.labels)
                                    };

                                    const chart = new ApexCharts(chartElement, {
                                        series: [{
                                            name: 'Availability',
                                            data: chartData.availability
                                        }, {
                                            name: 'Performance',
                                            data: chartData.performance
                                        }, {
                                            name: 'Quality',
                                            data: chartData.quality
                                        }, {
                                            name: 'OEE Score',
                                            data: chartData.oee
                                        }],
                                        chart: {
                                            type: 'area',
                                            height: 350,
                                            zoom: {
                                                enabled: false
                                            },
                                            toolbar: {
                                                show: true
                                            }
                                        },
                                        dataLabels: {
                                            enabled: false
                                        },
                                        stroke: {
                                            curve: 'smooth',
                                            width: 2
                                        },
                                        colors: ['#2eca6a', '#4154f1', '#ff771d', '#7928ca'],
                                        fill: {
                                            type: 'gradient',
                                            gradient: {
                                                shadeIntensity: 1,
                                                opacityFrom: 0.4,
                                                opacityTo: 0.2,
                                                stops: [0, 90, 100]
                                            }
                                        },
                                        xaxis: {
                                            categories: chartData.labels,
                                            title: {
                                                text: 'Date'
                                            }
                                        }
                                    });
                                    
                                    chart.render();

                                    // Listen for Livewire updates
                                    Livewire.on('chartDataUpdated', (newData) => {
                                        chart.updateOptions({
                                            xaxis: {
                                                categories: newData.labels
                                            }
                                        });
                                        chart.updateSeries([{
                                            data: newData.availability
                                        }, {
                                            data: newData.performance
                                        }, {
                                            data: newData.quality
                                        }, {
                                            data: newData.oee
                                        }]);
                                    });
                                });
                            </script>
                        </div>
                    </div>
                </div>
            </div>

            <!-- After the chart card -->
                <div class="card mt-4">
                    <div class="card-body">
                        <h5 class="card-title">Penjelasan Detail Perhitungan OEE</h5>

                        <!-- Availability Rate Explanation -->
                        <div class="calculation-section mb-4">
                            <h6 class="fw-bold">1. Availability Rate ({{ $averageAvailability }}%)</h6>
                            <p class="text-muted">Mengukur ketersediaan mesin untuk beroperasi</p>
                            
                            <div class="formula-box p-3 bg-light rounded mb-3">
                                <p class="mb-2">Rumus: (Operating Time ÷ Planned Production Time) × 100</p>
                                <p class="mb-2">= ({{ number_format($operatingTime) }} ÷ {{ number_format($plannedProductionTime) }}) × 100</p>
                                <p class="mb-0 fw-bold">= {{ number_format($averageAvailability, 2) }}%</p>
                            </div>
                        
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tr>
                                        <td width="30%">Planned Production Time</td>
                                        <td width="20%" class="text-end">{{ number_format($plannedProductionTime) }} menit</td>
                                        <td width="50%">Total waktu yang direncanakan untuk produksi (waktu shift)</td>
                                    </tr>
                                    <tr>
                                        <td>Downtime Problems</td>
                                        <td class="text-end">{{ number_format($downtimeProblems) }} menit</td>
                                        <td>Total waktu berhenti karena masalah teknis</td>
                                    </tr>
                                    <tr>
                                        <td>Downtime Maintenance</td>
                                        <td class="text-end">{{ number_format($downtimeMaintenance) }} menit</td>
                                        <td>Total waktu berhenti untuk pemeliharaan</td>
                                    </tr>
                                    <tr>
                                        <td>Total Downtime</td>
                                        <td class="text-end">{{ number_format($totalDowntime) }} menit</td>
                                        <td>Total waktu berhenti (Problems + Maintenance)</td>
                                    </tr>
                                    <tr>
                                        <td>Operating Time</td>
                                        <td class="text-end">{{ number_format($operatingTime) }} menit</td>
                                        <td>Waktu aktual mesin beroperasi (Planned Time - Total Downtime)</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    
                        <!-- Performance Rate Explanation -->
                        <div class="calculation-section mb-4">
                            <h6 class="fw-bold">2. Performance Rate ({{ $averagePerformance }}%)</h6>
                            <p class="text-muted">Mengukur kecepatan aktual mesin dibandingkan dengan kecepatan idealnya</p>
                            
                            <div class="formula-box p-3 bg-light rounded mb-3">
                                <p class="mb-2">Rumus: ((Total Output × Ideal Cycle Time) ÷ Operating Time) × 100</p>
                                <p class="mb-2">= (({{ number_format($totalOutput) }} × {{ number_format($idealCycleTime, 2) }}) ÷ {{ number_format($operatingTime) }}) × 100</p>
                                <p class="mb-0 fw-bold">= {{ number_format($averagePerformance, 2) }}%</p>
                            </div>
                        
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tr>
                                        <td width="30%">Total Output</td>
                                        <td width="20%" class="text-end">{{ number_format($totalOutput) }} unit</td>
                                        <td width="50%">Jumlah total produk yang dihasilkan</td>
                                    </tr>
                                    <tr>
                                        <td>Ideal Cycle Time</td>
                                        <td class="text-end">{{ number_format($idealCycleTime, 2) }} menit</td>
                                        <td>Waktu standar untuk memproduksi 1 unit (dari master data produk)</td>
                                    </tr>
                                    <tr>
                                        <td>Operating Time</td>
                                        <td class="text-end">{{ number_format($operatingTime) }} menit</td>
                                        <td>Waktu aktual mesin beroperasi</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    
                        <!-- Quality Rate Explanation -->
                        <div class="calculation-section mb-4">
                            <h6 class="fw-bold">3. Quality Rate ({{ $averageQuality }}%)</h6>
                            <p class="text-muted">Mengukur persentase produk yang memenuhi standar kualitas</p>
                            
                            <div class="formula-box p-3 bg-light rounded mb-3">
                                <p class="mb-2">Rumus: (Good Output ÷ Total Output) × 100</p>
                                <p class="mb-2">= ({{ number_format($goodOutput) }} ÷ {{ number_format($totalOutput) }}) × 100</p>
                                <p class="mb-0 fw-bold">= {{ number_format($averageQuality, 2) }}%</p>
                            </div>
                        
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tr>
                                        <td width="30%">Total Output</td>
                                        <td width="20%" class="text-end">{{ number_format($totalOutput) }} unit</td>
                                        <td width="50%">Jumlah total produk yang dihasilkan</td>
                                    </tr>
                                    <tr>
                                        <td>Defect Amount</td>
                                        <td class="text-end">{{ number_format($defectCount) }} unit</td>
                                        <td>Jumlah produk yang cacat/reject</td>
                                    </tr>
                                    <tr>
                                        <td>Good Output</td>
                                        <td class="text-end">{{ number_format($goodOutput) }} unit</td>
                                        <td>Jumlah produk yang memenuhi standar kualitas</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    
                        <!-- OEE Score Explanation -->
                        <div class="calculation-section">
                            <h6 class="fw-bold">4. Overall Equipment Effectiveness ({{ $oeeScore }}%)</h6>
                            <p class="text-muted">Nilai akhir yang menunjukkan efektivitas keseluruhan mesin</p>
                            
                            <div class="formula-box p-3 bg-light rounded mb-3">
                                <p class="mb-2">Rumus: Availability × Performance × Quality ÷ 10000</p>
                                <p class="mb-2">= {{ number_format($averageAvailability, 2) }}% × {{ number_format($averagePerformance, 2) }}% × {{ number_format($averageQuality, 2) }}% ÷ 10000</p>
                                <p class="mb-0 fw-bold">= {{ number_format($oeeScore, 2) }}%</p>
                            </div>
                        
                            <div class="alert alert-info">
                                <h6 class="fw-bold">Interpretasi Nilai OEE:</h6>
                                <ul class="mb-0">
                                    <li>< 60%: Performa buruk, perlu perbaikan segera</li>
                                    <li>60-85%: Performa cukup, masih ada ruang untuk improvement</li>
                                    <li>> 85%: Performa baik, pertahankan dan tingkatkan</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    </div>
</div>
