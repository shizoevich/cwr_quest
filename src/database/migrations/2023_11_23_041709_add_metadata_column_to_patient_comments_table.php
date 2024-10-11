<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMetadataColumnToPatientCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_comments', function (Blueprint $table) {
            $table->text('metadata')->nullable()->after('appointment_id')->comment('To store additional information for comments');
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
            $table->dropColumn('metadata');
        });
    }
}
