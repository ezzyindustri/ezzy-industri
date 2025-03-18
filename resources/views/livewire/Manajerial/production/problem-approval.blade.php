<div wire:poll.5s>
    <div class="pagetitle">
        <h1>Problem Approval</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('manajerial.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active">Problem Approval</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Daftar Problem Produksi</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Mesin</th>
                                <th>Tipe Problem</th>
                                <th>Catatan</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($problems as $problem)
                                <tr>
                                    <td>{{ $problem->reported_at->setTimezone('Asia/Jakarta')->format('d M Y H:i') }}</td>
                                    <td>{{ $problem->production->machine_name }}</td>
                                    <td>{{ ucfirst($problem->problem_type) }}</td>
                                    <td>{{ $problem->notes }}</td>
                                    <td>
                                        <span class="badge bg-{{ 
                                            $problem->status === 'pending' ? 'warning' : 
                                            ($problem->status === 'approved' ? 'success' : 'danger') 
                                        }}">
                                            {{ ucfirst($problem->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($problem->status === 'pending')
                                            <button class="btn btn-sm btn-success" wire:click="approve({{ $problem->id }})">
                                                <i class="bi bi-check-circle"></i> Approve
                                            </button>
                                            <button class="btn btn-sm btn-danger" wire:click="reject({{ $problem->id }})">
                                                <i class="bi bi-x-circle"></i> Reject
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">Tidak ada problem yang perlu diapprove</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

