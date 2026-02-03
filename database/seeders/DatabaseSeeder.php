<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $now = now();

        $holdingId = DB::table('companies')->insertGetId([
            'name' => 'Focus Group Capital',
            'parent_id' => null,
            'type' => 'holding',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $companyIds = [];
        $companyNames = ['MCB', 'JDC', 'MDC', 'DF', 'FTC'];

        foreach ($companyNames as $name) {
            $companyIds[] = DB::table('companies')->insertGetId([
                'name' => $name,
                'parent_id' => $holdingId,
                'type' => 'company',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('users')->insert([
            'name' => 'Finance Holding',
            'email' => 'finance.holding@example.com',
            'password' => Hash::make('password'),
            'role' => 'finance_holding',
            'company_id' => $holdingId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        foreach ($companyIds as $index => $companyId) {
            $number = $index + 1;

            DB::table('users')->insert([
                'name' => "Admin Company {$number}",
                'email' => "admin.company{$number}@example.com",
                'password' => Hash::make('password'),
                'role' => 'admin_company',
                'company_id' => $companyId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
