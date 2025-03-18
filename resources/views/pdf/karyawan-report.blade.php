<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Kinerja Karyawan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .performance-excellent {
            color: #28a745;
        }
        .performance-good {
            color: #17a2b8;
        }
        .performance-fair {
            color: #ffc107;
        }
        .performance-poor {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan Kinerja Karyawan</h2>
        <p>Periode: {{ $dateFrom }} - {{ $dateTo }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Nama Karyawan</th>
                <th>Departemen</th>
                <th>Quality Check</th>
                <th>PM/AM Check</th>
                <th>Produksi</th>
                <th>Performance</th>
            </tr>
        </thead>
        <tbody>
            @foreach($karyawan as $k)
            <tr>
                <td>{{ $k->name }}</td>
                <td>{{ $k->department->name ?? '-' }}</td>
                <td>
                    Completed: {{ $k->qc_metrics['completed'] ?? 0 }}/{{ $k->qc_metrics['required'] ?? 0 }}
                    <br>
                    Rate: {{ $k->qc_metrics['compliance_rate'] ?? 0 }}%
                </td>
                <td>
                    Rate: {{ $k->maintenance_metrics['rate'] ?? 0 }}%
                </td>
                <td>
                    Total: {{ $k->production_metrics['total_runs'] ?? 0 }}
                    <br>
                    Achievement: {{ $k->production_metrics['achievement_rate'] ?? 0 }}%
                    <br>
                    Problems: {{ $k->production_metrics['problem_frequency'] ?? 0 }}
                </td>
                <td>
                    @php
                        $qcRate = $k->qc_metrics['compliance_rate'] ?? 0;
                        $maintenanceRate = $k->maintenance_metrics['rate'] ?? 0;
                        $productionRate = $k->production_metrics['achievement_rate'] ?? 0;
                        $performanceRate = ($qcRate + $maintenanceRate + $productionRate) / 3;
                    @endphp
                    <span class="performance-{{ $performanceRate >= 90 ? 'excellent' : ($performanceRate >= 75 ? 'good' : ($performanceRate >= 60 ? 'fair' : 'poor')) }}">
                        {{ number_format($performanceRate, 1) }}%
                        ({{ $performanceRate >= 90 ? 'Excellent' : ($performanceRate >= 75 ? 'Good' : ($performanceRate >= 60 ? 'Fair' : 'Poor')) }})
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 20px;">
        <p><strong>Generated:</strong> {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>