<?php

use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table("users")->insert([
        	'name' => "Chanthou",
        	'email' => "chanthou_choub@hotmail.com",
        	'password' => bcrypt("123")
        ]);
    }
}
