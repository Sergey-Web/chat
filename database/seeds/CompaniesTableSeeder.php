<?php

use Illuminate\Database\Seeder;
use App\Company;

class CompaniesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Company::create([
            'name'   => 'com1',
            'domain' => 'com1'
        ]);

        Company::create([
            'name'   => 'com2',
            'domain' => 'com2'
        ]);

        Company::create([
            'name'   => 'com3',
            'domain' => 'com3'
        ]);

        Company::create([
            'name'   => 'com4',
            'domain' => 'com4'
        ]);
    }
}
