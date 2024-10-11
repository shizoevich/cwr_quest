<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAdditionalColumnsToPatientAlertsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_alerts', function (Blueprint $table) {
            $table->integer('deductible_met')->default(0)->after('deductible');
            $table->integer('deductible_remaining')->default(0)->after('deductible_met');
            $table->string('reference_number')->nullable()->after('insurance_pay');
            $table->unsignedInteger('patient_document_id')->nullable()->after('reference_number');

            $table->foreign('patient_document_id')
                ->references('id')
                ->on('patient_documents')
                ->onDelete('set null')
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
        Schema::table('patient_alerts', function (Blueprint $table) {
            $table->dropForeign(['patient_document_id']);
            $table->dropColumn('patient_document_id');
            $table->dropColumn('reference_number');
            $table->dropColumn('deductible_remaining');
            $table->dropColumn('deductible_met');
        });
    }
}
