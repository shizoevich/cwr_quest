<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToSubmittedReauthorizationRequestFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('submitted_reauthorization_request_forms', function (Blueprint $table) {
            $table->integer('patient_id')->nullable()->after('document_type');
            $table->integer('submitted_by')->unsigned()->nullable()->after('patient_id');

            $table->foreign('patient_id')
                ->references('id')
                ->on('patients')
                ->onDelete('SET NULL')
                ->onUpdate('CASCADE');

            $table->foreign('submitted_by')
                ->references('id')
                ->on('users')
                ->onDelete('SET NULL')
                ->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('submitted_reauthorization_request_forms', function (Blueprint $table) {
            $table->dropForeign(['patient_id']);
            $table->dropForeign(['submitted_by']);
            $table->dropColumn('patient_id', 'submitted_by');
        });
    }
}
