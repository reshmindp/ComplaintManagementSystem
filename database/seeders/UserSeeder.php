<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'phone' => '+1234567890',
            'is_active' => true,
        ]);
        $admin->assignRole('admin');

        // Create technician users
        $technician1 = User::create([
            'name' => 'John Technician',
            'email' => 'tech1@example.com',
            'password' => Hash::make('password'),
            'phone' => '+1234567891',
            'is_active' => true,
        ]);
        $technician1->assignRole('technician');

        $technician2 = User::create([
            'name' => 'Jane Technician',
            'email' => 'tech2@example.com',
            'password' => Hash::make('password'),
            'phone' => '+1234567892',
            'is_active' => true,
        ]);
        $technician2->assignRole('technician');

        // Create regular users
        User::factory()
            ->count(10)
            ->create()
            ->each(function ($user) {
                $user->assignRole('user');
            });
    }
}
