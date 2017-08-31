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
            'domain' => 'dom1'
        ]);

        Company::create([
            'name'   => 'com2',
            'domain' => 'dom2'
        ]);

        Company::create([
            'name'   => 'com3',
            'domain' => 'dom3'
        ]);

        Company::create([
            'name'   => 'com4',
            'domain' => 'dom4'
        ]);
    }
}
