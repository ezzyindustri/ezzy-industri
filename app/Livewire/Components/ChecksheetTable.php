<?php

namespace App\Livewire\Components;

use App\Models\MaintenanceTask;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;  
use App\Models\Production;
use App\Models\Machine;
use App\Models\ProductionCheck;     // Add this
use App\Models\ProductionSopCheck;  // Add this
use App\Models\ChecksheetEntry;
use App\Models\Sop;
use Illuminate\Support\Facades\Redirect;
use App\Models\Shift;

class ChecksheetTable extends Component
{
    use WithFileUploads;
    public $tasks;
    public $machineId;
    public $shiftId;
    public $machineSop;
    public $sopResults = [];
    public $checkResults = [];  // Add this
    public $notes = [];        // Add this
    public $photos = [];       // Add this
    
    
    public function mount($machineId, $shiftId)
    {
        $this->machineId = $machineId;
        $this->shiftId = $shiftId;
        
        // Get tasks based on frequency
        $this->tasks = $this->getTasksByFrequency();
        
        // Load SOP for this machine
        $this->machineSop = Sop::where('machine_id', $machineId)
                              ->where('is_active', true)
                              ->with(['steps' => function($query) {
                                  $query->where('is_checkpoint', true)
                                       ->orderBy('urutan');
                              }])
                              ->latest()
                              ->first();
    }

    private function getTasksByFrequency()
    {
        $tasks = MaintenanceTask::where('machine_id', $this->machineId)
            ->where('is_active', true)
            ->get();
    
        return $tasks->filter(function($task) {
            $lastCheck = ChecksheetEntry::where('task_id', $task->id)
                ->latest()
                ->first();
    
            $shouldShow = true;
            
            if ($lastCheck) {
                $lastCheckDate = $lastCheck->created_at;
                $today = now();
                
                switch ($task->frequency) {
                    case 'weekly':
                        $shouldShow = $lastCheckDate->diffInWeeks($today) >= 1;
                        Log::info("Weekly Task {$task->task_name}: Last check was on {$lastCheckDate}. Should show: " . ($shouldShow ? 'Yes' : 'No'));
                        break;
                        
                    case 'monthly':
                        $shouldShow = $lastCheckDate->diffInMonths($today) >= 1;
                        Log::info("Monthly Task {$task->task_name}: Last check was on {$lastCheckDate}. Should show: " . ($shouldShow ? 'Yes' : 'No'));
                        break;
                        
                    case 'daily':
                        $shouldShow = $lastCheckDate->diffInDays($today) >= 1;
                        Log::info("Daily Task {$task->task_name}: Last check was on {$lastCheckDate}. Should show: " . ($shouldShow ? 'Yes' : 'No'));
                        break;
                }
            } else {
                Log::info("Task {$task->task_name}: No previous check found. Showing task.");
            }
            
            return $shouldShow;
        });
    }

    public function render()
    {
        $sopCheckpoints = collect([]); 
    
        if ($this->machineSop) {
            $sopCheckpoints = $this->machineSop->steps ?? collect([]);
        }
    
        return view('livewire.components.checksheet-table', [
            'tasks' => $this->tasks,
            'maintenanceTasks' => $this->tasks, // keep this for backward compatibility
            'sopCheckpoints' => $sopCheckpoints
        ]);
    }

    public function startProduction()
    {
        try {
            Log::info('Starting production process');
            
            DB::beginTransaction();
            
            $pendingProduction = session('pending_production');
            
            // Create production record
            $production = Production::create([
                'user_id' => Auth::id(),
                'machine_id' => $this->machineId,
                'machine' => Machine::find($this->machineId)->name,
                'product_id' => $pendingProduction['product_id'],
                'product' => $pendingProduction['product'],
                'target_per_shift' => $pendingProduction['target_per_shift'],
                'shift_id' => $this->shiftId,
                'status' => 'running',
                'start_time' => now(),
            ]);
    
            // HAPUS loop pertama ini karena duplikat
            // foreach ($this->checkResults as $taskId => $result) {
            //     ChecksheetEntry::create([...]);
            // }
    
            // Gunakan hanya satu loop ini dengan logging
            // Di dalam method startProduction()
            foreach ($this->checkResults as $taskId => $result) {
                $task = MaintenanceTask::find($taskId);
                
                // Get shift start time for next check
                $shiftIds = json_decode($task->shift_ids);
                $shift = Shift::find($shiftIds[0]); // Get first assigned shift
                $nextCheckTime = now()->addDay()->format('Y-m-d') . ' ' . $shift->start_time;
                
                // Subtract 1 hour from shift start time
                $nextCheckDue = \Carbon\Carbon::parse($nextCheckTime)->subHour();
                
                $checksheetEntry = ChecksheetEntry::create([
                    'production_id' => $production->id,
                    'task_id' => $taskId,
                    'machine_id' => $this->machineId,
                    'shift_id' => $this->shiftId,
                    'user_id' => Auth::id(),
                    'result' => $result,
                    'notes' => $this->notes[$taskId] ?? null,
                    'photo_path' => isset($this->photos[$taskId]) ? $this->photos[$taskId]->store('photos', 'public') : null,
                ]);
    
                Log::info("Task Check Completed", [
                    'task_name' => $task->task_name,
                    'frequency' => $task->frequency,
                    'check_date' => $checksheetEntry->created_at->format('Y-m-d H:i:s'),
                    'next_check_due' => match($task->frequency) {
                        'daily' => $nextCheckDue->format('Y-m-d H:i:s'),
                        'weekly' => $nextCheckDue->addWeeks(1)->format('Y-m-d H:i:s'),
                        'monthly' => $nextCheckDue->addMonths(1)->format('Y-m-d H:i:s'),
                        default => 'unknown'
                    }
                ]);
            }
    
            DB::commit();
            
            return redirect()->route('production.status');
    
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in startProduction: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan saat memulai produksi');
            return null;
        }
    }

    public function getProgressPercentage()
    {
        if (!$this->tasks || $this->tasks->count() === 0) {
            return 0;
        }
        return min(100, round((count($this->checkResults) / $this->tasks->count()) * 100));
    }

    public function getProgressText()
    {
        return count($this->checkResults) . '/' . ($this->tasks?->count() ?? 0);
    }
    public function getProgressProperty()
    {
        if (!$this->tasks || $this->tasks->count() === 0) {
            return 0;
        }
        
        return round((count($this->checkResults) / $this->tasks->count()) * 100);
    }

    public function back()
    {
        return redirect()->route('production.start');
    }
}