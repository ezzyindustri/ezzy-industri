<?php

namespace App\Traits;

use App\Notifications\OeeAlertNotification;
use Illuminate\Support\Facades\Notification;

trait OeeAlertTrait
{
    protected function checkAndSendOeeAlert($machine, $oeeScore)
    {
        if (!$machine->alert_enabled || empty($machine->alert_email)) {
            return;
        }

        if ($oeeScore < $machine->oee_target) {
            Notification::route('mail', $machine->alert_email)
                ->notify(new OeeAlertNotification($machine, $oeeScore, $machine->oee_target));
        }
    }
}