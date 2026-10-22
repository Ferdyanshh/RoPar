<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::query()->updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@robotpacar.test')],
            [
                'name' => env('ADMIN_NAME', 'Admin RoPar'),
                'password' => Hash::make(env('ADMIN_PASSWORD', 'password')),
                'role' => UserRole::Admin,
            ],
        );

        $admin->profile()->updateOrCreate(
            ['user_id' => $admin->id],
            [
                'full_name' => env('ADMIN_NAME', 'Admin RoPar'),
                'phone' => '081234567890',
            ],
        );
    }
}
