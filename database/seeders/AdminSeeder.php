<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminSeeder extends Seeder
{
    public function run()
    {
        // Vérifie si l'admin existe déjà
        if (!User::where('username','admin')->exists()) {
            User::create([
                'username' => 'admin',
                'email'    => 'admin@edutec.com',
                'password' => Hash::make('admin123'),
                'role'     => 'admin',
            ]);
        }
    }
}
