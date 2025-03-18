<?php

namespace App\Http\Controllers;

use App\Models\Sop;
use Illuminate\Http\Request;
use PDF;

class SopPdfController extends Controller
{
    public function generate($id)
    {
        $sop = Sop::with(['steps', 'creator', 'approver', 'machine', 'product'])->findOrFail($id);
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.sop', compact('sop'));
        
        return $pdf->stream("SOP-{$sop->no_sop}.pdf");
    }
}