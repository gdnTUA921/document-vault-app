<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Run departments first
        $this->call(DepartmentSeeder::class);

        // --- Admins (no department) ---
        User::create([
            'first_name'    => 'Alice',
            'last_name'     => 'Johnson',
            'email'         => 'alice.johnson@example.com',
            'password'      => Hash::make('password'),
            'role'          => 'admin',
            'department_id' => null,
        ]);

        User::create([
            'first_name'    => 'Bob',
            'last_name'     => 'Smith',
            'email'         => 'bob.smith@example.com',
            'password'      => Hash::make('password'),
            'role'          => 'admin',
            'department_id' => null,
        ]);

        // --- Staff (with department) ---
        User::create([
            'first_name'    => 'Carol',
            'last_name'     => 'Davis',
            'email'         => 'carol.davis@example.com',
            'password'      => Hash::make('password'),
            'role'          => 'staff',
            'department_id' => 1, // IT Department
        ]);

        User::create([
            'first_name'    => 'David',
            'last_name'     => 'Martinez',
            'email'         => 'david.martinez@example.com',
            'password'      => Hash::make('password'),
            'role'          => 'staff',
            'department_id' => 2, // HR Department
        ]);

        // --- Users (with department) ---
        User::create([
            'first_name'    => 'Eve',
            'last_name'     => 'Wilson',
            'email'         => 'eve.wilson@example.com',
            'password'      => Hash::make('password'),
            'role'          => 'user',
            'department_id' => 1, // IT Department
        ]);

        User::create([
            'first_name'    => 'Frank',
            'last_name'     => 'Brown',
            'email'         => 'frank.brown@example.com',
            'password'      => Hash::make('password'),
            'role'          => 'user',
            'department_id' => 3, // Finance Department
        ]);
    }
}
