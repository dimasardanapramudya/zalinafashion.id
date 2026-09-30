<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['Aisyah Customer', 'aisyah@example.com'],
            ['Nabila Customer', 'nabila@example.com'],
            ['Siti Customer', 'siti@example.com'],
        ] as [$name, $email]) {
            User::updateOrCreate(['email' => $email], ['name' => $name, 'password' => 'Customer123!', 'role' => 'customer']);
        }
    }
}
