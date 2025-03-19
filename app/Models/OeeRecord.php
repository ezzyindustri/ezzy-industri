<?php

namespace App\Models;

use App\Notifications\OeeAlertNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\DowntimeRecord;


class OeeRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'machine_id',
        'production_id',
        'shift_id',
        'date',
        'planned_production_time',
        'operating_time',
        'downtime_problems',
        'downtime_maintenance',
        'total_downtime',
        'total_output',
        'target_output',
        'defect_count',
        'availability',
        'performance',
        'quality',
        'oee_score',
        'is_initial_record',
        'good_output',
        'availability_rate',
        'performance_rate',
        'quality_rate',
    ];

    // Relasi yang sudah ada...

    /**
     * Update OEE record dengan data terbaru dari produksi
     * 
     * @param int $productionId
     * @return OeeRecord|null
     */
    public static function updateFromProduction($productionId)
    {
        try {
            $production = Production::find($productionId);
            if (!$production) {
                Log::error("Production not found for ID: {$productionId}");
                return null;
            }

            $machine = Machine::find($production->machine_id);
            if (!$machine) {
                Log::error("Machine not found for production ID: {$productionId}");
                return null;
            }

            // Cari OEE record yang ada
            $oeeRecord = self::where('production_id', $productionId)->first();
            if (!$oeeRecord) {
                Log::error("OEE record not found for production ID: {$productionId}");
                return null;
            }

            // Hitung OEE components
            
            // 1. Planned Production Time (waktu produksi yang direncanakan)
            $startTime = Carbon::parse($production->start_time);
            $endTime = $production->end_time ? Carbon::parse($production->end_time) : Carbon::now();
            $plannedProductionTime = max(1, $endTime->diffInMinutes($startTime)); // Minimal 1 menit untuk menghindari division by zero
            
            // 2. Downtime (waktu henti)
            // Ambil dari tabel problems untuk downtime problems
            $downtimeProblems = ProductionProblem::where('production_id', $productionId)
                ->sum('duration') ?? 0;
                
            // Ambil dari tabel downtime_records untuk downtime maintenance
            // Gunakan oee_record_id bukan production_id
            $downtimeMaintenance = DowntimeRecord::where('oee_record_id', $oeeRecord->id)
                ->sum('duration') ?? 0;
                
            $totalDowntime = $downtimeProblems + $downtimeMaintenance;
            
            // 3. Operating Time (waktu operasi)
            $operatingTime = max(1, $plannedProductionTime - $totalDowntime); // Minimal 1 menit
            
            // 4. Total Output & Target Output
            $totalOutput = $production->total_output ?? 0;
            
            // 5. Defect Count - Ambil langsung dari tabel quality_checks
            $defectCount = QualityCheck::where('production_id', $productionId)
                ->sum('defect_count') ?? 0;
                
            // Log data quality check untuk debugging
            $qualityChecksCount = QualityCheck::where('production_id', $productionId)->count();
            Log::info("Quality check data for production ID: {$productionId}", [
                'total_checks' => $qualityChecksCount,
                'total_defects' => $defectCount
            ]);
            
            // Hitung target output berdasarkan standard speed mesin dan operating time
            $targetOutput = $production->target_output ?? 0;
            if ($targetOutput == 0 && $operatingTime > 0 && $machine->standard_speed > 0) {
                // Konversi operating time dari menit ke jam untuk perhitungan yang lebih akurat
                $operatingTimeHours = $operatingTime / 60;
                $targetOutput = max(1, $operatingTimeHours * $machine->standard_speed);
            }
            
            // PENTING: Jika total_output masih 0, gunakan defect_count sebagai total_output
            // Ini memastikan bahwa jika ada defect, maka pasti ada output
            if ($totalOutput == 0 && $defectCount > 0) {
                $totalOutput = $defectCount;
                Log::info("Setting total_output to defect_count: {$defectCount}");
            }
            
            // Pastikan total_output minimal 1 jika ada produksi yang sudah selesai
            if ($production->status == 'finished' && $totalOutput == 0) {
                $totalOutput = max(1, $defectCount); // Minimal sama dengan defect count atau 1
            }
            
            // Pastikan defect tidak melebihi total output
            $defectCount = min($defectCount, $totalOutput);
            
            // Hitung good output (output yang baik)
            $goodOutput = max(0, $totalOutput - $defectCount);
            
            // Hitung OEE metrics dengan pengecekan untuk menghindari division by zero
            $availability = $plannedProductionTime > 0 ? ($operatingTime / $plannedProductionTime) * 100 : 0;
            $performance = ($operatingTime > 0 && $targetOutput > 0) ? min(100, ($totalOutput / $targetOutput) * 100) : 0;
            
            // Perbaikan perhitungan quality - pastikan tidak division by zero
            $quality = $totalOutput > 0 ? (($goodOutput / $totalOutput) * 100) : 0;
            
            // Jika semua output adalah defect, tetapkan quality ke nilai minimal (misalnya 1%)
            if ($quality == 0 && $totalOutput > 0) {
                $quality = 1; // Nilai minimal untuk quality
            }
            
            // Batasi nilai maksimum 100% untuk masing-masing komponen
            $availability = min(100, $availability);
            $performance = min(100, $performance);
            $quality = min(100, $quality);
            
            // Hitung OEE score
            $oeeScore = ($availability / 100) * ($performance / 100) * ($quality / 100) * 100;
            
            // Log nilai-nilai untuk debugging
            Log::info("OEE Calculation Details", [
                'production_id' => $productionId,
                'planned_production_time' => $plannedProductionTime,
                'operating_time' => $operatingTime,
                'total_downtime' => $totalDowntime,
                'total_output' => $totalOutput,
                'target_output' => $targetOutput,
                'defect_count' => $defectCount,
                'good_output' => $goodOutput,
                'availability' => $availability,
                'performance' => $performance,
                'quality' => $quality,
                'oee_score' => $oeeScore
            ]);

            // Update OEE record
            $oeeRecord->update([
                'planned_production_time' => $plannedProductionTime,
                'operating_time' => $operatingTime,
                'downtime_problems' => $downtimeProblems,
                'downtime_maintenance' => $downtimeMaintenance,
                'total_downtime' => $totalDowntime,
                'total_output' => $totalOutput,
                'target_output' => $targetOutput,
                'defect_count' => $defectCount,
                'good_output' => $goodOutput,
                'availability_rate' => $availability / 100,
                'performance_rate' => $performance / 100,
                'quality_rate' => $quality / 100,
                'availability' => $availability,
                'performance' => $performance,
                'quality' => $quality,
                'oee_score' => $oeeScore,
                'is_initial_record' => false, // Mark as updated
            ]);

            Log::info("OEE record updated for production ID: {$productionId}", [
                'oee_score' => $oeeScore,
                'availability' => $availability,
                'performance' => $performance,
                'quality' => $quality
            ]);

            return $oeeRecord;
        } catch (\Exception $e) {
            Log::error("Error updating OEE record: " . $e->getMessage(), [
                'production_id' => $productionId,
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Check OEE alert conditions and send notifications if needed
     * 
     * @param int $productionId
     * @return bool
     */
    public static function checkAndSendAlerts($productionId)
    {
        try {
            // Pastikan OEE record diperbarui terlebih dahulu
            $oeeRecord = self::updateFromProduction($productionId);
            if (!$oeeRecord) {
                Log::error("Failed to update OEE record for alert check, production ID: {$productionId}");
                return false;
            }

            $production = Production::find($productionId);
            $machine = Machine::find($production->machine_id);
            
            // Ambil target OEE dari machine settings
            $targetOee = $machine->oee_target ?? 85.00;
            
            Log::info("OEE Record found", [
                'oee_score' => $oeeRecord->oee_score,
                'is_initial_record' => $oeeRecord->is_initial_record
            ]);

            // Cek apakah OEE di bawah target
            if ($oeeRecord->oee_score < $targetOee) {
                Log::info("OEE below target, sending notification", [
                    'oee_score' => $oeeRecord->oee_score,
                    'target' => number_format($targetOee, 2)
                ]);

                // Kirim notifikasi
                return self::sendOeeAlerts($machine, $oeeRecord->oee_score, $targetOee, $productionId);
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Error checking OEE alerts: " . $e->getMessage(), [
                'production_id' => $productionId,
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    // Metode lain yang sudah ada...
}