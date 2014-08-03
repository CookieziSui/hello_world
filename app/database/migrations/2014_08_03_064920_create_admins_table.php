<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('admins',function($table)
		{
			$table->increments('ID');
			$table->string('NAME');
			$table->string('EMAIL',64);
			$table->string('PASSWORD',64);
		});
		DB::table('admins')->insert(array(
			'NAME'=>'your name',
			'EMAIL'=>'your email',
			'password'=>Hash::make('your password'),
		));
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('admins');
	}

}
