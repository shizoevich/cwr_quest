<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateDefaultStartDateValue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       \DB::statement("ALTER TABLE `kaiser_appointments`
	CHANGE COLUMN `start_date` `start_date` TIMESTAMP NULL DEFAULT NULL AFTER `tridiuum_id`;
");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
