<?php

	use Illuminate\Database\Seeder;


	class UsersTableSeeder extends Seeder {

    public function run() {
    	$this->createUser('root', 'ankhzet@gmail.com', 'password')->roles()->sync([Role::ADMIN, Role::GAMEMASTER]);
    }


    function createUser($name, $email, $password) {
    	$user = new User;
    	$user->name = $name;
    	$user->email = $email;
    	$user->password = Hash::make($password);
    	$user->save();

    	return $user;
    }
	}
