<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Kinerja Karyawan</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 1.5cm;
        }
        body { 
            font-family: sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }
        .header-section {
            text-align: center;
            border-bottom: 2px solid #000;
            margin-bottom: 15px;
            padding-bottom: 10px;
        }
        .company-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px;
        }
        th {
            background-color: #f0f0f0;
        }
        .achievement {
            color: #059669;
            font-weight: bold;
        }
        .warning {
            color: #d97706;
            font-weight: bold;
        }
        .failure {
            color: #dc2626;
            font-weight: bold;
        }
        .problem-reject {
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div class="header-section">
        <div class="company-name">PT. EZZY INDUSTRI</div>
        <small>Kantor Cabang Ampenan</small>
        <h2>Laporan Kinerja Karyawan</h2>
        <p>Periode: {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} - {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}</p>
    </div>

    <!-- Sisanya tetap sama -->
    @php
        $metrics = [
            'qc' => $karyawan->getQualityMetrics($dateFrom, $dateTo),
            'maintenance' => $karyawan->getMaintenanceMetrics($dateFrom, $dateTo),
            'production' => $karyawan->getProductionMetrics($dateFrom, $dateTo)
        ];
        
        $performanceRate = round(($metrics['qc']['compliance_rate'] + 
                               $metrics['maintenance']['compliance_rate'] + 
                               $metrics['production']['achievement_rate']) / 3, 1);

        // Add this daily records calculation
        $dailyRecords = $karyawan->productions()
            ->whereBetween('start_time', [
                $dateFrom . ' 00:00:00',
                $dateTo . ' 23:59:59'
            ])
            ->get()
            ->groupBy(function($production) {
                return $production->start_time->format('Y-m-d');
            });
    @endphp

    <table class="summary-table">
        <tr>
            <th>Nama</th>
            <td>{{ $karyawan->name }}</td>
            <th>Departemen</th>
            <td>{{ $karyawan->department->name }}</td>
            <th>Performance Rate</th>
            <td class="{{ $performanceRate >= 90 ? 'achievement' : ($performanceRate >= 60 ? 'warning' : 'failure') }}">
                {{ $performanceRate }}%
            </td>
        </tr>
    </table>

    <!-- Daily Performance Table -->
    <table class="daily-records">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Produksi</th>
                <th>Quality Check</th>
                <th>Maintenance</th>
                <th width="40%">Masalah & Reject</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dailyRecords as $date => $productions)
            <tr>
                <td>{{ \Carbon\Carbon::parse($date)->format('d M Y') }}</td>
                <td>
                    @php
                        $productionMetrics = $karyawan->getProductionMetrics($date, $date);
                    @endphp
                    <span class="{{ $productionMetrics['achievement_rate'] == 100 ? 'achievement' : 'failure' }}">
                        {{ $productionMetrics['achievement_rate'] }}%
                        @if($productionMetrics['achievement_rate'] != 100)
                            <br>Target: {{ $productions->sum('target_per_shift') }}
                            <br>Actual: {{ $productions->sum('total_production') }}
                        @endif
                    </span>
                </td>
                <td>
                    @php
                        $qcMetrics = $karyawan->getQualityMetrics($date, $date);
                    @endphp
                    <span class="{{ $qcMetrics['compliance_rate'] == 100 ? 'achievement' : 'failure' }}">
                        {{ $qcMetrics['completed'] }}/{{ $qcMetrics['required'] }}
                        ({{ $qcMetrics['compliance_rate'] }}%)
                    </span>
                </td>
                <td>
                    @php
                        $maintenanceMetrics = $karyawan->getMaintenanceMetrics($date, $date);
                    @endphp
                    <span class="{{ $maintenanceMetrics['compliance_rate'] == 100 ? 'achievement' : 'failure' }}">
                        AM: {{ $maintenanceMetrics['completed_am'] }}
                        PM: {{ $maintenanceMetrics['completed_pm'] }}
                        ({{ $maintenanceMetrics['compliance_rate'] }}%)
                    </span>
                </td>
                <td class="problem-reject">
                    @php
                        $problems = $productions->flatMap->problems;
                        $rejects = $karyawan->getRejects($date, $date);
                    @endphp
                    @foreach($problems as $problem)
                        <div>
                            <strong>PROBLEM [{{ $problem->created_at->format('H:i') }}]:</strong>
                            Status: {{ ucfirst($problem->status) }} | 
                            Durasi: {{ $problem->resolved_at ? $problem->created_at->diffForHumans($problem->resolved_at, true) : 'Ongoing' }}<br>
                            {{ $problem->description }}
                        </div>
                    @endforeach
                    @foreach($rejects as $reject)
                        <div>
                            <strong>REJECT [{{ $reject->check_time->format('H:i') }}]:</strong>
                            {{ $reject->defect_count }} {{ $reject->defect_type }} | {{ $reject->defect_notes }}
                            @foreach($reject->details()->where('status', 'ng')->get() as $detail)
                                <br>• {{ $detail->parameter }}: {{ $detail->measured_value }} 
                                ({{ $detail->tolerance_min }} - {{ $detail->tolerance_max }})
                            @endforeach
                        </div>
                    @endforeach
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>