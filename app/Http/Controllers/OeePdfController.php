<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Models\OeeRecord;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class OeePdfController extends Controller
{
    public function generateDashboardPdf(Request $request)
    {
        $startDate = $request->startDate;
        $endDate = $request->endDate;
        $machines = Machine::with(['oeeRecords' => function($query) use ($startDate, $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }])->get();

        $pdf = Pdf::loadView('pdf.oee-pdf', [
            'machines' => $machines,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);

        return $pdf->stream("oee-report-{$startDate}-to-{$endDate}.pdf");
    }

    public function generateDetailPdf($machineId, Request $request)
    {
        $machine = Machine::findOrFail($machineId);
        $period = $request->period ?? 'daily';
        
        $query = OeeRecord::where('machine_id', $machineId);
        
        switch ($period) {
            case 'daily':
                $query->whereDate('date', Carbon::today());
                break;
            case 'weekly':
                $query->whereBetween('date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                break;
            case 'monthly':
                $query->whereMonth('date', Carbon::now()->month)
                      ->whereYear('date', Carbon::now()->year);
                break;
        }

        $records = $query->get();
        $latestRecord = $records->last();

        $data = [
            'machine' => $machine,
            'period' => $period,
            'averageAvailability' => round($records->avg('availability_rate') ?? 0, 2),
            'averagePerformance' => round($records->avg('performance_rate') ?? 0, 2),
            'averageQuality' => round($records->avg('quality_rate') ?? 0, 2),
            'oeeScore' => round($records->avg('oee_score') ?? 0, 2),
            'latestRecord' => $latestRecord,
            'records' => $records
        ];

        $pdf = Pdf::loadView('pdf.oee-detail-pdf', $data)
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'defaultFont' => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true
            ]);

        return $pdf->stream("oee-detail-{$machine->name}-{$period}.pdf");
    }
}