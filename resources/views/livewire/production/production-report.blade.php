<div>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Production Report Preview</h5>
            
            <div class="text-end mb-3">
                <button wire:click="downloadPdf" class="btn btn-primary">
                    <i class="bi bi-download"></i> Download PDF
                </button>
            </div>

            <div class="preview-content">
                <!-- Production Details -->
                <div class="mb-4">
                    <h6>Production Details</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tr>
                                <th width="200">Machine</th>
                                <td>
                                    @if(is_string($production->machine))
                                        {{ $production->machine }}
                                    @else
                                        {{ $production->machine->name ?? 'N/A' }}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Product</th>
                                <td>{{ $production->product }}</td> <!-- Changed from $production->product->name -->
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
                </div>

                <!-- Pre-Production Checksheet -->
                <div class="mb-4">
                    <h6>Pre-Production Checksheet</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
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
                                            <span class="text-secondary">N/A</span>
                                        @endif
                                    </td>
                                    <td>{{ $entry->notes ?? '-' }}</td>
                                </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">Tidak ada data checksheet</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Production Problems -->
                <div class="mb-4">
                    <h6>Production Problems</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
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
                                            {{ \Carbon\Carbon::parse($problem->reported_at)->diffInMinutes(\Carbon\Carbon::parse($problem->resolved_at)) }} minutes
                                        @elseif($problem->reported_at && $problem->status != 'resolved')
                                            {{ \Carbon\Carbon::parse($problem->reported_at)->diffInMinutes(now()) }} minutes (Ongoing)
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $problem->notes }}</td>
                                </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">Tidak ada masalah produksi</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Production Downtimes -->
                <div class="mb-4">
                    <h6>Production Downtimes</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Reason</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th>Duration (minutes)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($production->productionDowntimes ?? [] as $downtime)
                                <tr>
                                    <td>{{ $downtime->reason }}</td>
                                    <td>{{ $downtime->start_time ? $downtime->start_time->format('Y-m-d H:i:s') : 'N/A' }}</td>
                                    <td>{{ $downtime->end_time ? $downtime->end_time->format('Y-m-d H:i:s') : 'N/A' }}</td>
                                    <td>{{ $downtime->duration_minutes ?? 'N/A' }}</td>
                                </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">Tidak ada data downtime</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Quality Checks -->
                <div class="mb-4">
                    <h6>Quality Check Results</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Parameter</th>
                                    <th>Measured Value</th>
                                    <th>Standard</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($production->qualityChecks ?? [] as $check)
                                    @foreach($check->details ?? [] as $detail)
                                    <tr>
                                        <td>{{ $detail->parameter }}</td>
                                        <td>{{ $detail->measured_value }}</td>
                                        <td>{{ $detail->standard_value }} ({{ $detail->tolerance_min }} - {{ $detail->tolerance_max }})</td>
                                        <td>{{ $detail->status }}</td>
                                    </tr>
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">Tidak ada data quality check</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>