<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmail extends Command
{
    protected $signature = 'mail:test';
    protected $description = 'Test email configuration';

    public function handle()
    {
        $this->info('Sending test email...');
        
        try {
            Mail::raw('Test email from Ezzy Industri', function($message) {
                $message->to('abdul.azizurrohman220803@gmail.com')
                        ->subject('Test Email');
            });
            
            $this->info('Test email sent successfully!');
        } catch (\Exception $e) {
            $this->error('Failed to send email: ' . $e->getMessage());
        }
    }
}