<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>SOP {{ $sop->no_sop }}</title>
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
        .main-border {
            border: 2px solid #000;
            padding: 10px;
            margin-bottom: 20px;
        }
        .header-section {
            display: flex;
            border-bottom: 2px solid #000;
            margin-bottom: 15px;
            padding-bottom: 10px;
        }
        .logo-section {
            width: 20%;
            border-right: 2px solid #000;
            text-align: center;
            padding: 10px;
        }
        .title-section {
            width: 80%;
            padding: 10px;
        }
        .company-name {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 5px;
        }
        .document-title {
            font-size: 14px;
            text-align: center;
            margin: 5px 0;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }
        .info-box {
            border: 1px solid #000;
            padding: 10px;
        }
        .info-box h3 {
            margin: 0 0 10px 0;
            font-size: 13px;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table th, .info-table td {
            border: 1px solid #000;
            padding: 5px;
        }
        .info-table th {
            width: 35%;
            background-color: #f0f0f0;
        }
        .content-box {
            border: 1px solid #000;
            padding: 10px;
            margin-bottom: 15px;
        }
        .steps-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .steps-table th, .steps-table td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
        }
        .steps-table th {
            background-color: #f0f0f0;
        }
        .step-image {
            max-width: 120px;
            max-height: 120px;
        }
        .footer-section {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-top: 20px;
        }
        .signature-box {
            border: 1px solid #000;
            padding: 10px;
            text-align: center;
        }
        .signature-space {
            height: 60px;
            border-bottom: 1px solid #000;
            margin: 10px 0;
        }
        .page-break {
            page-break-before: always;
        }
        .section-title {
            background-color: #f0f0f0;
            padding: 8px;
            margin: 0;
            border: 1px solid #000;
            font-weight: bold;
            text-align: center;
        }
        .info-container {
            border: 1px solid #000;
            margin-bottom: 15px;
        }
        .info-content {
            padding: 10px;
        }
    </style>
</head>
<body>
    <!-- Halaman 1: Informasi SOP -->
    <div class="main-border">
        <div class="header-section">
            <div class="logo-section">
                <div class="company-name">PT. EZZY INDUSTRI</div>
                <small>Kantor Cabang Ampenan</small>
            </div>
            <div class="title-section">
                <table class="info-table">
                    <tr>
                        <th>Nomor SOP</th>
                        <td>{{ $sop->no_sop }}</td>
                    </tr>
                    <tr>
                        <th>Tanggal Pembuatan</th>
                        <td>{{ $sop->created_date ? $sop->created_date->format('d/m/Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <th>Tanggal Revisi</th>
                        <td>-</td>
                    </tr>
                    <tr>
                        <th>Tanggal Efektif</th>
                        <td>{{ $sop->approved_at ? $sop->approved_at->format('d/m/Y') : '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-box">
                <h3>INFORMASI SOP</h3>
                <table class="info-table">
                    <tr>
                        <th>NAMA SOP</th>
                        <td>{{ $sop->nama }}</td>
                    </tr>
                    <tr>
                        <th>KATEGORI</th>
                        <td>{{ ucfirst($sop->kategori) }}</td>
                    </tr>
                    @if($sop->kategori === 'produksi' || $sop->kategori === 'safety')
                    <tr>
                        <th>MESIN</th>
                        <td>{{ $sop->machine->name ?? '-' }}</td>
                    </tr>
                    @endif
                    <tr>
                        <th>STATUS</th>
                        <td>{{ ucfirst($sop->approval_status) }} {{ $sop->is_active ? '(Active)' : '(Inactive)' }}</td>
                    </tr>
                </table>
            </div>
            <div class="info-box">
                <h3>APPROVAL INFORMATION</h3>
                <table class="info-table">
                    <tr>
                        <th>DIBUAT OLEH</th>
                        <td>{{ $sop->creator->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>DIPERIKSA OLEH</th>
                        <td>Supervisor</td>
                    </tr>
                    <tr>
                        <th>DISETUJUI OLEH</th>
                        <td>{{ $sop->approver->name ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="content-box">
            <h3>{{ $sop->kategori === 'quality' ? 'PARAMETER QUALITY CHECK' : 'LANGKAH-LANGKAH PROSEDUR' }}</h3>
            <table class="steps-table">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="25%">{{ $sop->kategori === 'quality' ? 'Parameter' : 'Langkah' }}</th>
                        <th>Deskripsi</th>
                        @if($sop->kategori === 'quality')
                            <th width="15%">Standar</th>
                        @endif
                        <th width="15%">Gambar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sop->steps as $step)
                    <tr>
                        <td>{{ $step->urutan }}</td>
                        <td>{{ $step->judul }}</td>
                        <td>{{ $step->deskripsi }}</td>
                        @if($sop->kategori === 'quality')
                            <td>
                                {{ $step->nilai_standar }}
                                @if($step->toleransi_min && $step->toleransi_max)
                                    ({{ $step->toleransi_min }} - {{ $step->toleransi_max }})
                                @endif
                                {{ $step->measurement_unit }}
                            </td>
                        @endif
                        <td>
                            @if($step->gambar_path)
                                <img src="{{ public_path('storage/' . $step->gambar_path) }}" 
                                     alt="Step Image" class="step-image">
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="footer-section">
            <div class="signature-box">
                <p>Dibuat oleh:</p>
                <div class="signature-space"></div>
                <p>{{ $sop->creator->name ?? '-' }}</p>
                <p>Tanggal: {{ $sop->created_date ? $sop->created_date->format('d/m/Y') : '-' }}</p>
            </div>
            <div class="signature-box">
                <p>Diperiksa oleh:</p>
                <div class="signature-space"></div>
                <p>Supervisor</p>
                <p>Tanggal: ........................</p>
            </div>
            <div class="signature-box">
                <p>Disetujui oleh:</p>
                <div class="signature-space"></div>
                <p>{{ $sop->approver->name ?? '-' }}</p>
                <p>Tanggal: {{ $sop->approved_at ? $sop->approved_at->format('d/m/Y') : '-' }}</p>
            </div>
        </div>
    </div>

    <!-- Halaman 2: Langkah-langkah -->
    <div class="page-break">
        <div class="main-border">
            <div class="header-section">
                <!-- Duplicate header untuk konsistensi -->
                <div class="logo-section">
                    <div class="company-name">PT. EZZY INDUSTRI</div>
                    <small>Kantor Cabang Ampenan</small>
                </div>
                <div class="title-section">
                    <table class="info-table">
                        <tr>
                            <th>Nomor SOP</th>
                            <td>{{ $sop->no_sop }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal Pembuatan</th>
                            <td>{{ $sop->created_date ? $sop->created_date->format('d/m/Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal Revisi</th>
                            <td>-</td>
                        </tr>
                        <tr>
                            <th>Tanggal Efektif</th>
                            <td>{{ $sop->approved_at ? $sop->approved_at->format('d/m/Y') : '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="info-container">
                <h3 class="section-title">{{ $sop->kategori === 'quality' ? 'PARAMETER QUALITY CHECK' : 'LANGKAH-LANGKAH PROSEDUR' }}</h3>
                <div class="info-content">
                    <table class="steps-table">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="25%">{{ $sop->kategori === 'quality' ? 'Parameter' : 'Langkah' }}</th>
                                <th>Deskripsi</th>
                                @if($sop->kategori === 'quality')
                                    <th width="15%">Standar</th>
                                @endif
                                <th width="15%">Gambar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sop->steps as $step)
                            <tr>
                                <td>{{ $step->urutan }}</td>
                                <td>{{ $step->judul }}</td>
                                <td>{{ $step->deskripsi }}</td>
                                @if($sop->kategori === 'quality')
                                    <td>
                                        {{ $step->nilai_standar }}
                                        @if($step->toleransi_min && $step->toleransi_max)
                                            ({{ $step->toleransi_min }} - {{ $step->toleransi_max }})
                                        @endif
                                        {{ $step->measurement_unit }}
                                    </td>
                                @endif
                                <td>
                                    @if($step->gambar_path)
                                        <img src="{{ public_path('storage/' . $step->gambar_path) }}" 
                                             alt="Step Image" class="step-image">
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Halaman 3: Approval -->
    <div class="page-break">
        <div class="main-border">
            <div class="header-section">
                <!-- Duplicate header untuk konsistensi -->
                <div class="logo-section">
                    <div class="company-name">PT. EZZY INDUSTRI</div>
                    <small>Kantor Cabang Ampenan</small>
                </div>
                <div class="title-section">
                    <table class="info-table">
                        <tr>
                            <th>Nomor SOP</th>
                            <td>{{ $sop->no_sop }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal Pembuatan</th>
                            <td>{{ $sop->created_date ? $sop->created_date->format('d/m/Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal Revisi</th>
                            <td>-</td>
                        </tr>
                        <tr>
                            <th>Tanggal Efektif</th>
                            <td>{{ $sop->approved_at ? $sop->approved_at->format('d/m/Y') : '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="info-container">
                <h3 class="section-title">APPROVAL</h3>
                <div class="info-content">
                    <div class="footer-section">
                        <p>Dibuat oleh:</p>
                        <div class="signature-space"></div>
                        <p>{{ $sop->creator->name ?? '-' }}</p>
                        <p>Tanggal: {{ $sop->created_date ? $sop->created_date->format('d/m/Y') : '-' }}</p>
                    </div>
                    <div class="signature-box">
                        <p>Diperiksa oleh:</p>
                        <div class="signature-space"></div>
                        <p>Supervisor</p>
                        <p>Tanggal: ........................</p>
                    </div>
                    <div class="signature-box">
                        <p>Disetujui oleh:</p>
                        <div class="signature-space"></div>
                        <p>{{ $sop->approver->name ?? '-' }}</p>
                        <p>Tanggal: {{ $sop->approved_at ? $sop->approved_at->format('d/m/Y') : '-' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>