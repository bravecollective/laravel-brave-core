<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateCoreAuthUsersTable
 */
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
			$table->integer('id')->unsigned()->primary();
			$table->string('token', 64)->index();
			$table->string('remember_token', 160)->index();
			$table->string('character_name', 255)->index();
			$table->integer('alliance_id')->unsigned()->index();
			$table->string('alliance_name', 255);
			$table->boolean('status')->index();
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
