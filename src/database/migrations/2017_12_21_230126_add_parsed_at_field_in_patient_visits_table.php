<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddParsedAtFieldInPatientVisitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_visits', function(Blueprint $table) {
            $table->integer('parsed_at')->nullable();
            $table->softDeletes();
        });
        Schema::table('patient_visit_billings', function(Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_visits', function(Blueprint $table) {
            $table->dropColumn('parsed_at');
            $table->dropColumn('deleted_at');
        });
        Schema::table('patient_visit_billings', function(Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
    }
}
