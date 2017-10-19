<?php

use Illuminate\Database\Seeder;
use App\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name'       => 'Oleg',
            'email'      => 'oleg@gmail.com',
            'password'   => bcrypt('test'),
            'company_id' => 1
        ]);

        User::create([
            'name'       => 'Genya',
            'email'      => 'genya@gmail.com',
            'password'   => bcrypt('test'),
            'company_id' => 1
        ]);

        User::create([
            'name'       => 'Ilya',
            'email'      => 'ilya@gmail.com',
            'password'   => bcrypt('test'),
            'company_id' => 2
        ]);

        User::create([
            'name'       => 'Max',
            'email'      => 'max@gmail.com',
            'password'   => bcrypt('test'),
            'company_id' => 2
        ]);

        User::create([
            'name'       => 'Dima',
            'email'      => 'dima@gmail.com',
            'password'   => bcrypt('test'),
            'company_id' => 3
        ]);

        User::create([
            'name'       => 'Anton',
            'email'      => 'anton@gmail.com',
            'password'   => bcrypt('test'),
            'company_id' => 4
        ]);

        User::create([
            'name'       => 'Valera',
            'email'      => 'valera@gmail.com',
            'password'   => bcrypt('test'),
            'company_id' => 1
        ]);

        User::create([
            'name'       => 'Andrey',
            'email'      => 'andrey@gmail.com',
            'password'   => bcrypt('test'),
            'company_id' => 4
        ]);

        User::create([
            'name'       => 'Igor',
            'email'      => 'igor@gmail.com',
            'password'   => bcrypt('test'),
            'company_id' => 2
        ]);

        User::create([
            'name'       => 'Vlad',
            'email'      => 'vlad@gmail.com',
            'password'   => bcrypt('test'),
            'company_id' => 3
        ]);

        User::create([
            'name'       => 'Tanya',
            'email'      => 'tanya@gmail.com',
            'password'   => bcrypt('test'),
            'company_id' => 4
        ]);

        User::create([
            'name'       => 'Lera',
            'email'      => 'lera@gmail.com',
            'password'   => bcrypt('test'),
            'company_id' => 1
        ]);

        User::create([
            'name'       => 'John',
            'email'      => 'john@gmail.com',
            'password'   => bcrypt('test'),
            'company_id' => 2
        ]);

        User::create([
            'name'       => 'Neo',
            'email'      => 'neo@gmail.com',
            'password'   => bcrypt('test'),
            'company_id' => 3
        ]);

        User::create([
            'name'       => 'Sonya',
            'email'      => 'sonya@gmail.com',
            'password'   => bcrypt('test'),
            'company_id' => 4
        ]);

        User::create([
            'name'       => 'Gerardo',
            'email'      => 'gerardo@gmail.com',
            'password'   => bcrypt('test'),
            'company_id' => 1
        ]);

        User::create([
            'name'       => 'Rony',
            'email'      => 'rony@gmail.com',
            'password'   => bcrypt('test'),
            'company_id' => 2
        ]);

        User::create([
            'name'       => 'Leo',
            'email'      => 'leo@gmail.com',
            'password'   => bcrypt('test'),
            'company_id' => 3
        ]);
    }
}
