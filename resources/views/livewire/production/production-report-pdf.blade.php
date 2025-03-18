<!DOCTYPE html>
<html>
<head>
    <title>Production Report</title>
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
            padding: 5px;
        }
        th { 
            background-color: #f4f4f4;
        }
        .section { 
            margin-bottom: 20px;
        }
        h3 {
            color: #333;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .text-success { color: green; }
        .text-danger { color: red; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Production Report</h2>
        <p>Date: {{ now()->format('d/m/Y') }}</p>
    </div>

    <!-- Production Details -->
    <div class="section">
        <h3>Production Details</h3>
        <table>
            <tr>
                <th width="30%">Machine</th>
                <td>{{ $production->machine }}</td>
            </tr>
            <tr>
                <th>Product</th>
                <td>{{ $production->product }}</td>
            </tr>
            <tr>
                <th>Start Time</th>
                <td>{{ $production->start_time->format('Y-m-d H:i:s') }}</td>
            </tr>
            <tr>
                <th>End Time</th>
                <td>{{ $production->end_time ? $production->end_time->format('Y-m-d H:i:s') : 'N/A' }}</td>
            </tr>
            <tr>
                <th>Total Production</th>
                <td>{{ $production->total_production ?? 0 }}</td>
            </tr>
            <tr>
                <th>Defect Count</th>
                <td>{{ $production->defect_count ?? 0 }}</td>
            </tr>
        </table>
    </div>

    <!-- Pre-Production Checksheet -->
    <div class="section">
        <h3>Pre-Production Checksheet</h3>
        <table>
            <thead>
                <tr>
                    <th>Task Name</th>
                    <th>Type</th>
                    <th>Result</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($production->checksheetEntries as $entry)
                <tr>
                    <td>{{ $entry->task->task_name }}</td>
                    <td>{{ strtoupper($entry->task->maintenance_type) }}</td>
                    <td>
                        @if($entry->result == 'ok')
                            <span class="text-success">OK</span>
                        @elseif($entry->result == 'not_ok')
                            <span class="text-danger">NOT OK</span>
                        @else
                            N/A
                        @endif
                    </td>
                    <td>{{ $entry->notes ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align: center">Tidak ada data checksheet</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Production Problems -->
    <div class="section">
        <h3>Production Problems</h3>
        <table>
            <thead>
                <tr>
                    <th>Problem Type</th>
                    <th>Status</th>
                    <th>Reported At</th>
                    <th>Resolved At</th>
                    <th>Duration</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($production->problems ?? [] as $problem)
                <tr>
                    <td>{{ ucfirst($problem->problem_type) }}</td>
                    <td>{{ ucfirst($problem->status) }}</td>
                    <td>{{ \Carbon\Carbon::parse($problem->reported_at)->format('Y-m-d H:i:s') }}</td>
                    <td>{{ $problem->resolved_at ? \Carbon\Carbon::parse($problem->resolved_at)->format('Y-m-d H:i:s') : '-' }}</td>
                    <td>
                        @if($problem->resolved_at && $problem->reported_at)
                            @php
                                $start = \Carbon\Carbon::parse($problem->reported_at);
                                $end = \Carbon\Carbon::parse($problem->resolved_at);
                                $duration = number_format($start->diffInSeconds($end), 3);
                                $parts = explode('.', $duration);
                                $seconds = $parts[0];
                                $milliseconds = isset($parts[1]) ? $parts[1] : '000';
                            @endphp
                            {{ $seconds }} detik {{ $milliseconds }} ms
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $problem->notes }}</td>
                </tr>
                @empty
                    <tr><td colspan="6" style="text-align: center">Tidak ada masalah produksi</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Production Downtimes -->
    <div class="section">
        <h3>Production Downtimes</h3>
        <table>
            <thead>
                <tr>
                    <th>Reason</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Duration</th>
                </tr>
            </thead>
            <tbody>
                @forelse($production->productionDowntimes as $productionDowntime)
                <tr>
                    <td>{{ $productionDowntime->reason }}</td>
                    <td>{{ $productionDowntime->start_time ? $productionDowntime->start_time->format('Y-m-d H:i:s') : 'N/A' }}</td>
                    <td>{{ $productionDowntime->end_time ? $productionDowntime->end_time->format('Y-m-d H:i:s') : 'N/A' }}</td>
                    <td>
                        @if($productionDowntime->duration_minutes)
                            @php
                                $duration = number_format($productionDowntime->duration_minutes, 3);
                                $parts = explode('.', $duration);
                                $seconds = $parts[0];
                                $milliseconds = isset($parts[1]) ? $parts[1] : '000';
                            @endphp
                            {{ $seconds }} detik {{ $milliseconds }} ms
                        @else
                            N/A
                        @endif
                    </td>
                </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center">Tidak ada data downtime</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

        <!-- Quality Check Results -->
        <div class="section">
            <h3>Quality Check Results</h3>
            <table>
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Sample Size</th>
                        <th>Parameter</th>
                        <th>Value</th>
                        <th>Tolerance</th>
                        <th>Status</th>
                        <th>Operator</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($production->qualityChecks as $check)
                        @foreach($check->details as $detail)
                            <tr>
                                @if($loop->first)
                                    <td rowspan="{{ $check->details->count() }}">
                                        {{ $check->check_time->format('Y-m-d H:i:s') }}
                                    </td>
                                    <td rowspan="{{ $check->details->count() }}">
                                        {{ $check->sample_size }}
                                    </td>
                                @endif
                                <td>{{ $detail->parameter }}</td>
                                <td>{{ $detail->measured_value }}</td>
                                <td>({{ $detail->tolerance_min }} - {{ $detail->tolerance_max }})</td>
                                <td class="{{ $detail->status === 'ok' ? 'text-success' : 'text-danger' }}">
                                    {{ strtoupper($detail->status) }}
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
                            <td colspan="7" style="text-align: center">Tidak ada data quality check</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>