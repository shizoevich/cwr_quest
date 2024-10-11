<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypeIdFieldInPatientCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_comments', function (Blueprint $table) {
            $table->integer('default_comment_id')->unsigned()->nullable();

            $table->foreign('default_comment_id')
                ->references('id')
                ->on('patient_default_comments')
                ->onDelete('cascade')
                ->onUpdate('cascade');
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
            $table->dropForeign(['default_comment_id']);
            $table->dropColumn('default_comment_id');
        });
    }
}
