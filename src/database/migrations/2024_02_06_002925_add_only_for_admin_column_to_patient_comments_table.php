<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOnlyForAdminColumnToPatientCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_comments', function (Blueprint $table) {
            $table->boolean('only_for_admin')->default(0)->after('default_comment_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_comments', function (Blueprint $table) {
            $table->dropColumn('only_for_admin');
        });
    }
}
