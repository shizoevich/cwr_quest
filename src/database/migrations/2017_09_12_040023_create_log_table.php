<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sql
            = "CREATE TABLE `log` (
	            `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	            `data` LONGTEXT NULL,
	            `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
	            PRIMARY KEY (`id`)
                )
              COLLATE='utf8_general_ci'
              ENGINE=InnoDB;"
        ;
        \DB::statement($sql);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('log');
    }
}
