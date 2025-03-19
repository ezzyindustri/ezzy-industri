<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Production;

class WhatsAppService
{
    protected $apiKey;
    protected $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('services.whatsapp.api_key');
        $this->apiUrl = config('services.whatsapp.api_url', 'https://api.fonnte.com/send');
        
        Log::info('WhatsApp service initialized with API key: ' . substr($this->apiKey, 0, 4) . '...' . substr($this->apiKey, -4));
    }

    public function sendOeeAlert($phone, $machine, $oeeScore, $targetOee, $production = null)
    {
        try {
            // Pastikan machine adalah objek atau string yang valid
            $machineName = is_object($machine) ? $machine->name : (is_string($machine) ? $machine : 'Unknown Machine');
            
            // Dapatkan status produksi jika ada
            $productionId = $production ? $production->id : null;
            $productionStatus = $production ? $production->status : 'N/A';
            
            Log::info('Attempting to send WhatsApp OEE Alert', [
                'phone' => $phone,
                'machine' => $machineName,
                'oee_score' => $oeeScore,
                'target_oee' => $targetOee,
                'production_id' => $productionId,
                'production_status' => $productionStatus,
                'api_url' => $this->apiUrl,
                'api_key_set' => $this->apiKey ? 'yes' : 'no'
            ]);

            // Format pesan
            $message = "*ALERT: OEE DI BAWAH TARGET*\n\n";
            $message .= "Mesin: *{$machineName}*\n";
            $message .= "OEE Score: *" . number_format($oeeScore, 2) . "%*\n";
            $message .= "Target OEE: *" . number_format($targetOee, 2) . "%*\n";
            $message .= "Selisih: *" . number_format($targetOee - $oeeScore, 2) . "%*\n";
            
            if ($production) {
                $message .= "\nProduction ID: *{$production->id}*\n";
                $message .= "Status: *" . ucfirst($production->status) . "*\n";
            }
            
            $message .= "\n\nSilakan periksa dashboard OEE untuk detail lebih lanjut.";
            
            Log::info('WhatsApp message prepared', [
                'phone' => $phone,
                'message' => $message
            ]);

            // Kirim pesan dengan SSL verification dimatikan
            $response = Http::withOptions([
                'verify' => false, // Matikan verifikasi SSL
            ])->withHeaders([
                'Authorization' => $this->apiKey
            ])->post($this->apiUrl, [
                'target' => $phone,
                'message' => $message,
            ]);
            
            $statusCode = $response->status();
            $body = $response->body();
            
            Log::info('WhatsApp API response', [
                'status_code' => $statusCode,
                'body' => $body,
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]);
            
            if ($statusCode == 200) {
                Log::info("OEE Alert WhatsApp sent for machine {$machineName} with score {$oeeScore}%");
                return true;
            } else {
                Log::error("Failed to send WhatsApp alert. Status code: {$statusCode}, Response: {$body}");
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Exception when sending WhatsApp message: ' . $e->getMessage());
            return false;
        }
    }
}