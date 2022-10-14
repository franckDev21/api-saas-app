<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        \App\Models\Company::factory(4)->create();
        \App\Models\User::factory(100)->create();
        \App\Models\User::factory()->create([
            'firstname' => 'All',
            'lastname' => 'H corp',
            'email' => 'info@allhcorp.com',
            'role' => 'SUPER',
            'as_company' => false
        ]);
        \App\Models\Customer::factory(10)->create();
    }
}
