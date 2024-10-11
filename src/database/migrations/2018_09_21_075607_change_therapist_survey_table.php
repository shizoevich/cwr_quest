<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeTherapistSurveyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('therapist_survey', function(Blueprint $table) {
            $table->string('middle_name')->nullable()->change();
            $table->string('credentials')->nullable()->change();
            $table->string('school')->nullable()->change();
            $table->date('complete_education')->nullable()->change();
            $table->integer('years_of_practice')->nullable()->change();
            $table->string('languages')->nullable()->change();
            $table->text('help_description')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('therapist_survey', function(Blueprint $table) {
            $table->string('middle_name')->change();
            $table->string('credentials')->change();
            $table->string('school')->change();
            $table->date('complete_education')->change();
            $table->integer('years_of_practice')->change();
            $table->string('languages')->change();
            $table->text('help_description')->change();
        });
    }
}
