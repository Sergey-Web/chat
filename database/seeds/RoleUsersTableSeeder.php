<?php

use Illuminate\Database\Seeder;
use App\RoleUser;

class RoleUsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        RoleUser::create([
           'role_id' => 1,
           'user_id' => 1
        ]);

        RoleUser::create([
            'role_id' => 1,
            'user_id' => 2
        ]);

        RoleUser::create([
            'role_id' => 1,
            'user_id' => 3
        ]);

        RoleUser::create([
            'role_id' => 1,
            'user_id' => 4
        ]);

        RoleUser::create([
            'role_id' => 1,
            'user_id' => 5
        ]);

        RoleUser::create([
            'role_id' => 1,
            'user_id' => 6
        ]);

        RoleUser::create([
            'role_id' => 2,
            'user_id' => 7
        ]);

        RoleUser::create([
            'role_id' => 2,
            'user_id' => 8
        ]);

        RoleUser::create([
            'role_id' => 2,
            'user_id' => 9
        ]);

        RoleUser::create([
            'role_id' => 2,
            'user_id' => 10
        ]);

        RoleUser::create([
            'role_id' => 2,
            'user_id' => 11
        ]);

        RoleUser::create([
            'role_id' => 2,
            'user_id' => 12
        ]);

        RoleUser::create([
            'role_id' => 2,
            'user_id' => 13
        ]);

        RoleUser::create([
            'role_id' => 2,
            'user_id' => 14
        ]);

        RoleUser::create([
            'role_id' => 2,
            'user_id' => 15
        ]);

        RoleUser::create([
            'role_id' => 2,
            'user_id' => 16
        ]);

        RoleUser::create([
            'role_id' => 2,
            'user_id' => 17
        ]);

        RoleUser::create([
            'role_id' => 2,
            'user_id' => 18
        ]);
    }
}
