<?php

namespace App\Traits;

use App\Notifications\OeeAlertNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;
use App\Models\OeeAlert;
use App\Models\Production;
use App\Models\OeeRecord;
use App\Services\WhatsAppService;

trait OeeAlertTrait
{
    protected function checkAndSendOeeAlert($machine, $oeeScore, $productionId = null)
    {
        try {
            // Tambahkan log untuk debugging dengan lebih detail
            Log::info("Checking OEE alert conditions", [
                'machine' => $machine->name,
                'oee_score' => $oeeScore,
                'target' => $machine->oee_target,
                'alert_enabled' => $machine->alert_enabled,
                'production_id' => $productionId,
                'alert_email' => $machine->alert_email,
                'alert_phone' => $machine->alert_phone,
                'is_test' => $productionId === null
            ]);

            // Jika alert tidak diaktifkan, keluar
            if (!$machine->alert_enabled) {
                Log::info("OEE Alert skipped: alerts not enabled for machine {$machine->name}");
                return false;
            }

            // Periksa apakah email atau nomor telepon dikonfigurasi
            if (empty($machine->alert_email) && empty($machine->alert_phone)) {
                Log::warning("OEE Alert skipped: no email or phone configured for machine {$machine->name}");
                return false;
            }

            // PENTING: Jika ada production ID, periksa apakah produksi sudah selesai
            if ($productionId) {
                $production = Production::find($productionId);
                
                // Log status produksi dengan detail
                Log::info("Production status check", [
                    'production_id' => $productionId,
                    'production_found' => $production ? 'yes' : 'no',
                    'end_time' => $production ? ($production->end_time ? $production->end_time->format('Y-m-d H:i:s') : 'not ended') : 'N/A',
                    'status' => $production ? $production->status : 'N/A'
                ]);
                
                // PERUBAHAN: Kirim notifikasi bahkan jika produksi belum selesai jika OEE sangat rendah (di bawah 10%)
                $isVeryLowOee = $oeeScore < 10;
                
                if (!$production || (!$production->end_time && !$isVeryLowOee)) {
                    Log::info("OEE Alert skipped: production not ended yet and OEE not critically low");
                    return false; // Produksi belum selesai, jangan kirim notifikasi kecuali OEE sangat rendah
                }
                
                // Check if we already sent an alert for this production
                $alertExists = OeeAlert::where('production_id', $productionId)
                    ->where('machine_id', $machine->id)
                    ->exists();
                
                if ($alertExists) {
                    Log::info("OEE Alert skipped: alert already sent for this production");
                    return false; // Alert already sent for this production
                }
            }

            // Check if OEE is below target
            if ($oeeScore < $machine->oee_target || $productionId === null) {
                Log::info("OEE is below target, preparing to send alerts", [
                    'oee_score' => $oeeScore,
                    'oee_target' => $machine->oee_target
                ]);
                
                // Log this alert to prevent duplicates
                if ($productionId) {
                    OeeAlert::create([
                        'production_id' => $productionId,
                        'machine_id' => $machine->id,
                        'oee_score' => $oeeScore,
                        'target_oee' => $machine->oee_target,
                        'sent_at' => now(),
                    ]);
                    Log::info("OEE Alert record created in database");
                }
                
                // Send email notification if email is configured
                if (!empty($machine->alert_email)) {
                    try {
                        Notification::route('mail', $machine->alert_email)
                            ->notify(new OeeAlertNotification($machine, $oeeScore, $machine->oee_target, $productionId));
                        
                        Log::info("OEE Alert email sent for machine {$machine->name} with score {$oeeScore}%");
                    } catch (\Exception $emailError) {
                        Log::error("Email error: " . $emailError->getMessage());
                    }
                } else {
                    Log::info("Email alert skipped: no email configured for machine {$machine->name}");
                }
                
                // Send WhatsApp notification if phone is configured
                if (!empty($machine->alert_phone)) {
                    Log::info("Preparing to send WhatsApp alert to {$machine->alert_phone}");
                    
                    $whatsappService = app(WhatsAppService::class);
                    $production = $productionId ? Production::find($productionId) : null;
                    
                    try {
                        $result = $whatsappService->sendOeeAlert(
                            $machine->alert_phone,
                            $machine,
                            $oeeScore,
                            $machine->oee_target,
                            $production
                        );
                        
                        if ($result) {
                            Log::info("OEE Alert WhatsApp sent for machine {$machine->name} with score {$oeeScore}%");
                        } else {
                            Log::error("Failed to send WhatsApp alert for machine {$machine->name}");
                        }
                    } catch (\Exception $whatsappError) {
                        Log::error("WhatsApp error: " . $whatsappError->getMessage());
                    }
                } else {
                    Log::info("WhatsApp alert skipped: no phone number configured for machine {$machine->name}");
                }
                
                return true;
            } else {
                Log::info("OEE Alert skipped: OEE score {$oeeScore} is not below target {$machine->oee_target}");
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Error sending OEE Alert: " . $e->getMessage());
            Log::error($e->getTraceAsString());
            return false;
        }
    }
}