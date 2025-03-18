<div>
    <div class="modal fade" id="downtimeModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Record Downtime</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form wire:submit="saveDowntime">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Downtime Type</label>
                            <select class="form-select" wire:model="reason" required>
                                <option value="">Select Type</option>
                                <option value="break">Break Time</option>
                                <option value="meeting">Meeting</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="setup">Machine Setup</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" wire:model="notes" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Start Downtime</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>