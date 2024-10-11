<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusCodeFieldInLogTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('log')) {
            Schema::table('log', function (Blueprint $table) {
                $table->integer('status_code')->nullable();
                $table->string('url')->nullable();
                $table->string('client_ip')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if(Schema::hasTable('log')) {
            Schema::table('log', function (Blueprint $table) {
                $table->dropColumn('status_code');
                $table->dropColumn('url');
                $table->dropColumn('client_ip');
            });
        }
    }
}
