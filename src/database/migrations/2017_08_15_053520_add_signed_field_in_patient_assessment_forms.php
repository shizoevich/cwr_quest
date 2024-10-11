<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSignedFieldInPatientAssessmentForms extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('patients_assessment_forms', function (Blueprint $table) {
            $table->boolean('has_signature')->default(false)->description('is template has field for signature');
            $table->boolean('signed')->default(false);
            $table->integer('creator_id')->unsigned()->index();

            $table->foreign('creator_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
        Schema::table('assessment_forms', function (Blueprint $table) {
            $table->boolean('has_signature')->default(false)->description('is template has field for signature');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        //
    }
}
