<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'admin'],     // id = 1
            ['name' => 'customer'],  // id = 2
            ['name' => 'staff'],     // id = 3
            ['name' => 'super_admin'],   // id = 4
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
