<?php

	use Illuminate\Database\Seeder;

	class RolesTableSeeder extends Seeder {
	
		public function run() {
			$this->createRole(Role::ADMIN, 'Administrator', 'Admin');
			$this->createRole(Role::MODERATOR, 'Moderator');
			$this->createRole(Role::GAMEMASTER, 'Game Master', 'GM');
			$this->createRole(Role::USER, 'User');
			$this->createRole(Role::MUTED, 'Muted');
			$this->createRole(Role::BANNED, 'Banned');
		}

		function createRole($id, $title, $label = null) {
			$role = new Role;
			$role->id = $id;
			$role->title = $title;
			$role->label = $label ?: $title;
			$role->save();
		}
	}
