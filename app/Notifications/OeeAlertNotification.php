<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Machine;
use App\Models\Production;
use Illuminate\Support\Facades\Log;

class OeeAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $machine;
    protected $oeeScore;
    protected $targetOee;
    protected $productionId;

    public function __construct($machine, $oeeScore, $targetOee, $productionId = null)
    {
        // Pastikan machine adalah objek Machine, bukan string
        if (is_string($machine)) {
            $this->machine = Machine::find($machine) ?? $machine;
        } else {
            $this->machine = $machine;
        }
        
        $this->oeeScore = $oeeScore;
        $this->targetOee = $targetOee;
        $this->productionId = $productionId;
        
        Log::info('OEE Alert Notification initialized', [
            'machine' => is_object($this->machine) ? $this->machine->name : $machine,
            'production_id' => $productionId,
            'production_found' => $productionId ? (Production::find($productionId) ? 'yes' : 'no') : 'N/A',
            'oee_score' => $oeeScore,
            'target_oee' => $targetOee
        ]);
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $production = null;
        if ($this->productionId) {
            $production = Production::find($this->productionId);
        }
        
        // Pastikan machine adalah objek dengan property name
        $machineName = is_object($this->machine) ? $this->machine->name : 'Unknown Machine';
        
        Log::info('Preparing OEE Alert email', [
            'to' => $notifiable->routes['mail'],
            'machine' => $machineName,
            'oee_score' => $this->oeeScore,
            'target_oee' => $this->targetOee
        ]);

        return (new MailMessage)
            ->subject('ALERT: OEE Di Bawah Target untuk ' . $machineName)
            ->view('emails.oee-alert', [
                'machine' => $this->machine,
                'machineName' => $machineName, // Tambahkan ini untuk template
                'oeeScore' => $this->oeeScore,
                'targetOee' => $this->targetOee,
                'production' => $production
            ]);
    }

    public function toArray($notifiable)
    {
        return [
            'machine_id' => is_object($this->machine) ? $this->machine->id : null,
            'machine_name' => is_object($this->machine) ? $this->machine->name : 'Unknown Machine',
            'oee_score' => $this->oeeScore,
            'target_oee' => $this->targetOee,
            'production_id' => $this->productionId
        ];
    }
}