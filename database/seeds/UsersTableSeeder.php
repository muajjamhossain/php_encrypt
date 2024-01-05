<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \Illuminate\Support\Facades\DB::table('users')->insert(array
        (   [
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'user_id'=>'admin',
                'password' => bcrypt('123456'),
                'status' =>1,
            ],
        ));
    }
}
