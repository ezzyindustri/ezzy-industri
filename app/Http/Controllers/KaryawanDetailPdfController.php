<?php

namespace App\Http\Controllers;

use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;  // Add this line

class KaryawanDetailPdfController extends Controller
{
    public function generate($userId, $dateFrom, $dateTo)   
    {
        $karyawan = User::with([
            'department',
            'productions.machine',
            'productions',
            'productions.problems',
            'productions.productionDowntimes',
            'qualityChecks',
            'qualityChecks.details',  // Tambahkan ini
            'qualityChecks.production',
            'checksheetEntries.task'
        ])->findOrFail($userId);

        $pdf = Pdf::loadView('pdf.karyawan-detail', [
            'karyawan' => $karyawan,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo
        ]);

        return $pdf->stream("laporan-kinerja-{$karyawan->name}.pdf");
    }

    public function generateReport($dateFrom, $dateTo, Request $request)
    {
        $query = User::with([
            'department',
            'productions.machine',
            'productions.problems',
            'productions.productionDowntimes',
            'qualityChecks.details',
            'qualityChecks.production',
            'checksheetEntries.task'
        ]);

        if ($request->department) {
            $query->where('department_id', $request->department);
        }

        $karyawan = $query->get();

        $pdf = Pdf::loadView('pdf.karyawan-report', [
            'karyawan' => $karyawan,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo
        ])
        ->setPaper('a4', 'landscape')
        ->setOptions([
            'defaultFont' => 'sans-serif',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true
        ]);

        return $pdf->stream("laporan-kinerja-karyawan.pdf");
    }
}