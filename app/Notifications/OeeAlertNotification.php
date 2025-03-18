<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class OeeAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $machine;
    protected $oeeScore;
    protected $targetOee;

    public function __construct($machine, $oeeScore, $targetOee)
    {
        $this->machine = $machine;
        $this->oeeScore = $oeeScore;
        $this->targetOee = $targetOee;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $difference = $this->targetOee - $this->oeeScore;
        
        return (new MailMessage)
            ->subject('OEE Alert: ' . $this->machine->name)
            ->line('OEE Score untuk mesin ' . $this->machine->name . ' berada di bawah target.')
            ->line('Current OEE: ' . number_format($this->oeeScore, 2) . '%')
            ->line('Target OEE: ' . number_format($this->targetOee, 2) . '%')
            ->line('Difference: -' . number_format($difference, 2) . '%')
            ->action('Lihat Detail', url('/manajerial/oee/' . $this->machine->id . '/detail'))
            ->line('Mohon segera lakukan pengecekan dan tindakan yang diperlukan.');
    }
}