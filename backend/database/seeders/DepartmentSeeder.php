<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        Department::create(['name' => 'IT Department']);
        Department::create(['name' => 'HR Department']);
        Department::create(['name' => 'Finance Department']);
    }
}
