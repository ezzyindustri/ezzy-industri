<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    public function run()
    {
        $departments = [
            'Production',
            'Quality Control',
            'Maintenance',
            'Engineering',
            'Warehouse'
        ];

        foreach ($departments as $dept) {
            Department::create(['name' => $dept]);
        }
    }
}