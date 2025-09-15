<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Kiểm tra nếu bảng roles đã có dữ liệu thì không chạy seeder
        if (Role::count() > 0) {
            return;
        }

        $roles = [
            ['name' => 'admin'],     // id = 1
            ['name' => 'customer'],  // id = 2
            ['name' => 'staff'],     // id = 3
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
