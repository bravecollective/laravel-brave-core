<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCoreAuthUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('core_auth_users', function(Blueprint $table)
		{
			$table->increments('id')->unsigned();
			$table->string('token', 64);
			$table->text('remember_token');
			$table->string('character_name', 128);
			$table->integer('alliance_id');
			$table->text('alliance_name');
			$table->text('tags');
			$table->integer('status');
			$table->integer('permission');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('core_auth_users');
	}

}
