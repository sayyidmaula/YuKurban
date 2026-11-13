<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $password = Hash::make('password');

        // 1 Admin
        User::updateOrCreate(
            ['email' => 'admin@yukurban.test'],
            [
                'name' => 'Administrator YuKurban',
                'password' => $password,
                'role' => Role::ADMIN,
                'phone' => '08110000001',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // 3 Peternak
        for ($i = 1; $i <= 3; $i++) {
            User::updateOrCreate(
                ['email' => "peternak{$i}@yukurban.test"],
                [
                    'name' => "Mitra Peternak {$i}",
                    'password' => $password,
                    'role' => Role::PETERNAK,
                    'phone' => '0812000000' . $i,
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
        }

        // 5 Penabung
        for ($i = 1; $i <= 5; $i++) {
            User::updateOrCreate(
                ['email' => "penabung{$i}@yukurban.test"],
                [
                    'name' => "Sdr. Penabung {$i}",
                    'password' => $password,
                    'role' => Role::PENABUNG,
                    'phone' => '0813000000' . $i,
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
