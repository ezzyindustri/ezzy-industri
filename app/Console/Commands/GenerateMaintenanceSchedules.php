<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MaintenanceTask;
use App\Models\MaintenanceSchedule;
use Carbon\Carbon;

class GenerateMaintenanceSchedules extends Command
{
    protected $signature = 'maintenance:generate-schedules';
    protected $description = 'Generate maintenance schedules based on task configurations';

    public function handle()
    {
        $tasks = MaintenanceTask::where('is_active', true)->get();
        $tomorrow = Carbon::tomorrow();

        foreach ($tasks as $task) {
            if (!$this->shouldGenerateSchedule($task, $tomorrow)) {
                continue;
            }

            foreach (json_decode($task->shift_ids) as $shiftId) {
                MaintenanceSchedule::create([
                    'machine_id' => $task->machine_id,
                    'task_id' => $task->id,
                    'shift_id' => $shiftId,
                    'maintenance_type' => $task->maintenance_type,
                    'schedule_date' => $tomorrow,
                    'status' => 'pending'
                ]);
            }
        }

        $this->info('Maintenance schedules generated successfully!');
    }

    private function shouldGenerateSchedule($task, $date)
    {
        return match ($task->frequency) {
            'daily' => true,
            'weekly' => $date->dayOfWeek === 1, // Setiap hari Senin
            'monthly' => $date->day === 1, // Setiap tanggal 1
            default => false,
        };
    }
}