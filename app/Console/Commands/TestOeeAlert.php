<?php

namespace App\Console\Commands;

use App\Models\Machine;
use App\Notifications\OeeAlertNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class TestOeeAlert extends Command
{
    protected $signature = 'oee:test-alert';
    protected $description = 'Test OEE Alert notification';

    public function handle()
    {
        $machine = Machine::first();
        if (!$machine) {
            $this->error('No machine found!');
            return;
        }

        try {
            $this->info('Sending test OEE alert...');
            // Perbaikan cara pengiriman notifikasi
            Notification::route('mail', $machine->alert_email)
                ->notify(new OeeAlertNotification(
                    $machine,
                    70.00,
                    $machine->oee_target
                ));
            
            $this->info('Test alert sent!');
        } catch (\Exception $e) {
            $this->error('Failed to send alert: ' . $e->getMessage());
        }
    }
}