<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {

		Schema::create('roles', function(Blueprint $table) {
			$table->increments('id');

			$table->string('Name', 50)->unique();
		});

		Schema::create('users', function(Blueprint $table) {
			$table->increments('id');
			$table->timestamps();
			$table->rememberToken();
			$table->softDeletes();

			$table->string('email')->unique();
			$table->string('password', 80);
			$table->string('nick', 32)->unique();

			$table->integer('role_id')->unsigned();
			$table->foreign('role_id')->references('id')->on('roles');
		});


	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::drop('users');
	}

}
