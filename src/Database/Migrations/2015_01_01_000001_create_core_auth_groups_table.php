<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateCoreAuthGroupsTable
 */
class CreateCoreAuthGroupsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('core_auth_groups', function(Blueprint $table)
		{
			$table->increments('id')->unsigned();
			$table->string('name')->unique();
			$table->timestamps();
		});

		Schema::create('core_auth_groups_map', function(Blueprint $table)
		{
			$table->integer('user_id')->unsigned();
			$table->integer('group_id')->unsigned();
			$table->timestamps();

			$table->primary(['user_id', 'group_id']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('core_auth_groups');
		Schema::drop('core_auth_groups_map');
	}

}
