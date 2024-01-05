<?php

use Illuminate\Database\Seeder;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = new \App\Role();
        $admin->name         = 'superadmin';
        $admin->display_name = 'Super Admin'; // optional
        $admin->description  = 'User is allowed to manage and edit other users'; // optional
        $admin->save();

        $roleUser = \Illuminate\Support\Facades\DB::table('model_has_roles')
            ->insert([
                'user_id' => 1,
                'role_id' => 1,
            ]);


    }
}
