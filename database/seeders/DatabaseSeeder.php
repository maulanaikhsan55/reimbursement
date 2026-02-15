<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            DepartemenSeeder::class,
            KategoriBiayaSeeder::class,
            CoaSeeder::class,
            KasBankSeeder::class,
            UserSeeder::class,
            DivisionRoleSeeder::class,
            PengajuanSeeder::class,
            // ValidasiAISeeder::class,
        ]);
    }
}
