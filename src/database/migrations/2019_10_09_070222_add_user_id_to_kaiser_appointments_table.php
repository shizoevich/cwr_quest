<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserIdToKaiserAppointmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('kaiser_appointments', function(Blueprint $table) {
            $table->unsignedInteger('user_id')->nullable()->after('status')->comment('Secretary user id who change appointment status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('kaiser_appointments', function(Blueprint $table) {
            $table->dropColumn('user_id');
        });
    }
}
