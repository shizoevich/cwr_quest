<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAppointmentIdColumnToOfficeAllyTransactionsTable extends Migration
{
    /** transaction_purpose_id_to_officeally_transactions
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('officeally_transactions', function (Blueprint $table) {
            $table->integer('appointment_id')->nullable()->default(null)->after('patient_id');

            $table->foreign('appointment_id')
                ->references('id')
                ->on('appointments')
                ->onDelete('SET NULL')
                ->onUpdate('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('officeally_transactions', function (Blueprint $table) {
            $table->dropForeign(['appointment_id']);
            $table->dropColumn('appointment_id');
        });
    }
}
