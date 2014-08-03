<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('docs',function($table)
		{
			$table->increments('ID');
			$table->string('TITLE',64);
			$table->text('CONTENT');
			$table->string('CREATE_DATE',12);
			$table->string('LAST_CHANGE',12);
		});
		DB::table('docs')->insert(array(
			'TITLE'=>'test',
			'CONTENT'=>'just a test!',
			'CREATE_DATE'=>time(),
			'LAST_CHANGE'=>'',
	    ));
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('docs');
	}
}
