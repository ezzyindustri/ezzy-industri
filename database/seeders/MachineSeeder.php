<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Machine;

class MachineSeeder extends Seeder
{
    public function run()
    {
        Machine::create([
            'name' => 'Mesin A',
            'code' => 'MSN-001',
            'type' => 'Mesin',
            'status' => 'active',
            'location' => 'Line 1'
        ]);

        Machine::create([
            'name' => 'Mesin B',
            'code' => 'MSN-002',
            'type' => 'Mesin',
            'status' => 'active',
            'location' => 'Line 2'
        ]);
    }
}