<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPatientLeadIdColumnToUserFaxLogActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_fax_log_activities', function (Blueprint $table) {
            $table->integer('patient_lead_id')->default(null)->nullable()->unsigned()->after('patient_id');

            $table->foreign('patient_lead_id')
                ->references('id')
                ->on('patient_leads')
                ->onDelete('NO ACTION')
                ->onUpdate('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_fax_log_activities', function (Blueprint $table) {
            $table->dropForeign(['patient_lead_id']);

            $table->dropColumn('patient_lead_id');
        });
    }
}
