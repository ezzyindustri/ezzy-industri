<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Machine;
use App\Traits\OeeAlertTrait;
use Illuminate\Support\Facades\Log;

class TestOeeNotification extends Command
{
    use OeeAlertTrait;

    protected $signature = 'oee:test-notification {machine_id?} {oee_score?}';
    protected $description = 'Test OEE notification without starting production';

    public function handle()
    {
        $machineId = $this->argument('machine_id');
        $oeeScore = $this->argument('oee_score');

        if (!$machineId) {
            // Tampilkan daftar mesin jika tidak ada ID yang diberikan
            $machines = Machine::all(['id', 'name', 'oee_target', 'alert_enabled', 'alert_email', 'alert_phone']);
            $this->table(
                ['ID', 'Name', 'OEE Target', 'Alert Enabled', 'Email', 'Phone'],
                $machines->map(function ($machine) {
                    return [
                        $machine->id,
                        $machine->name,
                        $machine->oee_target,
                        $machine->alert_enabled ? 'Yes' : 'No',
                        $machine->alert_email ?: 'Not set',
                        $machine->alert_phone ?: 'Not set',
                    ];
                })
            );

            $machineId = $this->ask('Enter machine ID to test notification');
        }

        $machine = Machine::find($machineId);
        if (!$machine) {
            $this->error("Machine with ID {$machineId} not found");
            return 1;
        }

        if (!$oeeScore) {
            $oeeScore = $this->ask('Enter OEE score to test (should be below ' . $machine->oee_target . ' to trigger notification)', $machine->oee_target - 10);
        }

        $oeeScore = (float) $oeeScore;

        $this->info("Testing OEE notification for machine: {$machine->name}");
        $this->info("OEE Score: {$oeeScore}%, Target: {$machine->oee_target}%");
        
        if (!$machine->alert_enabled) {
            $this->warn("Alert is not enabled for this machine. Enabling temporarily for test...");
        }
        
        if (empty($machine->alert_email) && empty($machine->alert_phone)) {
            $this->error("No email or phone configured for this machine. Please configure at least one.");
            return 1;
        }

        Log::info("Starting OEE notification test", [
            'machine' => $machine->name,
            'oee_score' => $oeeScore,
            'target' => $machine->oee_target
        ]);

        try {
            // Panggil metode dari trait
            $result = $this->checkAndSendOeeAlert($machine, $oeeScore);
            
            if ($result) {
                $this->info("Notification test completed successfully!");
            } else {
                $this->warn("Notification might not have been sent. Check the logs for details.");
            }
            
            return 0;
        } catch (\Exception $e) {
            $this->error("Error sending notification: " . $e->getMessage());
            Log::error("Error in OEE notification test: " . $e->getMessage());
            Log::error($e->getTraceAsString());
            return 1;
        }
    }
}