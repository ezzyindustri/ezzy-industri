<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>OEE Report</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px; 
        }
        th, td { 
            border: 1px solid #ddd; 
            padding: 8px; 
            text-align: left; 
        }
        th { 
            background-color: #f2f2f2; 
        }
        .header { 
            margin-bottom: 20px; 
        }
        .badge {
            padding: 5px 10px;
            border-radius: 4px;
            color: white;
            font-weight: bold;
        }
        .bg-danger { 
            background-color: #dc3545; 
            color: white;
        }
        .bg-warning { 
            background-color: #ffc107; 
            color: black;
        }
        .bg-success { 
            background-color: #28a745; 
            color: white;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan Overall Equipment Effectiveness (OEE)</h2>
        <p>Periode: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Mesin</th>
                <th>Availability</th>
                <th>Performance</th>
                <th>Quality</th>
                <th>OEE Score</th>
            </tr>
        </thead>
        <tbody>
            @foreach($machines as $machine)
            <tr>
                <td>{{ $machine['name'] }}</td>
                <td class="{{ $machine['availability_rate'] < 60 ? 'bg-danger' : ($machine['availability_rate'] < 85 ? 'bg-warning' : 'bg-success') }}">
                    {{ $machine['availability_rate'] }}%
                </td>
                <td class="{{ $machine['performance_rate'] < 60 ? 'bg-danger' : ($machine['performance_rate'] < 85 ? 'bg-warning' : 'bg-success') }}">
                    {{ $machine['performance_rate'] }}%
                </td>
                <td class="{{ $machine['quality_rate'] < 60 ? 'bg-danger' : ($machine['quality_rate'] < 85 ? 'bg-warning' : 'bg-success') }}">
                    {{ $machine['quality_rate'] }}%
                </td>
                <td class="{{ $machine['oee_score'] < 60 ? 'bg-danger' : ($machine['oee_score'] < 85 ? 'bg-warning' : 'bg-success') }}">
                    {{ $machine['oee_score'] }}%
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>