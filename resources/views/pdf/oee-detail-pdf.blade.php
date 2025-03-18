<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>OEE Detail Report - {{ $machine->name }}</title>
    <style>
        body { 
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        .metric-card {
            margin-bottom: 20px;
            padding: 10px;
            border: 1px solid #ddd;
        }
        .formula-box {
            background-color: #f9f9f9;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #eee;
        }
        .text-danger { color: #dc3545; }
        .text-warning { color: #ffc107; }
        .text-success { color: #28a745; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Detail OEE Report - {{ $machine->name }}</h2>
        <p>Period: {{ ucfirst($period) }} - {{ now()->format('d/m/Y') }}</p>
    </div>

    <!-- OEE Summary -->
    <table>
        <tr>
            <th>Metric</th>
            <th>Value</th>
            <th>Status</th>
        </tr>
        <tr>
            <td>Availability Rate</td>
            <td>{{ $averageAvailability }}%</td>
            <td>{{ $averageAvailability < 60 ? 'Poor' : ($averageAvailability < 85 ? 'Fair' : 'Good') }}</td>
        </tr>
        <tr>
            <td>Performance Rate</td>
            <td>{{ $averagePerformance }}%</td>
            <td>{{ $averagePerformance < 60 ? 'Poor' : ($averagePerformance < 85 ? 'Fair' : 'Good') }}</td>
        </tr>
        <tr>
            <td>Quality Rate</td>
            <td>{{ $averageQuality }}%</td>
            <td>{{ $averageQuality < 60 ? 'Poor' : ($averageQuality < 85 ? 'Fair' : 'Good') }}</td>
        </tr>
        <tr>
            <td>OEE Score</td>
            <td>{{ $oeeScore }}%</td>
            <td>{{ $oeeScore < 60 ? 'Poor' : ($oeeScore < 85 ? 'Fair' : 'Good') }}</td>
        </tr>
    </table>

    <!-- Detailed Calculations -->
    @if($latestRecord)
    <h3>Detailed Calculations</h3>

    <!-- Availability Details -->
    <div class="metric-card">
        <h4>1. Availability Rate ({{ $averageAvailability }}%)</h4>
        <table>
            <tr>
                <td>Planned Production Time</td>
                <td>{{ number_format($latestRecord->planned_production_time) }} minutes</td>
            </tr>
            <tr>
                <td>Total Downtime</td>
                <td>{{ number_format($latestRecord->total_downtime) }} minutes</td>
            </tr>
            <tr>
                <td>Operating Time</td>
                <td>{{ number_format($latestRecord->operating_time) }} minutes</td>
            </tr>
        </table>
    </div>

    <!-- Performance Details -->
    <div class="metric-card">
        <h4>2. Performance Rate ({{ $averagePerformance }}%)</h4>
        <table>
            <tr>
                <td>Total Output</td>
                <td>{{ number_format($latestRecord->total_output) }} units</td>
            </tr>
            <tr>
                <td>Ideal Cycle Time</td>
                <td>{{ number_format($latestRecord->ideal_cycle_time, 2) }} minutes/unit</td>
            </tr>
            <tr>
                <td>Operating Time</td>
                <td>{{ number_format($latestRecord->operating_time) }} minutes</td>
            </tr>
        </table>
    </div>

    <!-- Quality Details -->
    <div class="metric-card">
        <h4>3. Quality Rate ({{ $averageQuality }}%)</h4>
        <table>
            <tr>
                <td>Total Output</td>
                <td>{{ number_format($latestRecord->total_output) }} units</td>
            </tr>
            <tr>
                <td>Defect Count</td>
                <td>{{ number_format($latestRecord->defect_count) }} units</td>
            </tr>
            <tr>
                <td>Good Output</td>
                <td>{{ number_format($latestRecord->good_output) }} units</td>
            </tr>
        </table>
    </div>
    @endif

    <div style="margin-top: 20px;">
        <p><strong>Generated on:</strong> {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>