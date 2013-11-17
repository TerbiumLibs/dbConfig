<?php

use Illuminate\Database\Migrations\Migration;

class CreateSettingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {

		Schema::create(
			'settings', function ($table) {

				$table->increments('id');
				$table->string('environment', 255);
				$table->string('key', 255)->index();
				$table->text('value');

				$table->unique(array('environment', 'key'));
			}
		);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {

		Schema::drop('settings');
	}

}