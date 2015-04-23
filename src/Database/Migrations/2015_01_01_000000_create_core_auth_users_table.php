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
			$table->increments('id')->unsigned();
			$table->string('token', 64)->index();
			$table->text('remember_token')->index();
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
