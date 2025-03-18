<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ChecksheetTask;
use App\Models\Machine;

class ChecksheetTaskSeeder extends Seeder
{
    public function run()
    {
        $machine = Machine::first();

        // AM Tasks
        ChecksheetTask::create([
            'machine_id' => $machine->id,
            'type' => 'am',
            'task_name' => 'Cek Pressure',
            'description' => 'Periksa tekanan mesin',
            'standard_value' => '6 bar',
            'min_value' => '5.5 bar',
            'max_value' => '6.5 bar',
            'order' => 1
        ]);

        ChecksheetTask::create([
            'machine_id' => $machine->id,
            'type' => 'am',
            'task_name' => 'Cek Oli',
            'description' => 'Periksa level dan kondisi oli',
            'standard_value' => 'Level normal',
            'order' => 2
        ]);

        // PM Tasks
        ChecksheetTask::create([
            'machine_id' => $machine->id,
            'type' => 'pm',
            'task_name' => 'Pembersihan Kipas',
            'description' => 'Bersihkan kipas dari debu dan kotoran',
            'order' => 1
        ]);
    }
}