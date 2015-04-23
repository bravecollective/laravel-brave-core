<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCoreAuthPermissionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('core_auth_permissions', function(Blueprint $table)
		{
			$table->increments('id')->unsigned();
			$table->text('name')->unique(512);
			$table->timestamps();
		});

		Schema::create('core_auth_permissions_map', function(Blueprint $table)
		{
			$table->integer('user_id')->unsigned()->index();
			$table->integer('permission_id')->unsigned()->index();
			$table->timestamps();

			$table->unique(['user_id', 'permission_id']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('core_auth_permissions');
		Schema::drop('core_auth_permissions_map');
	}

}
