<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVisitFrequencyIdColumnToPatientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->tinyInteger('visit_frequency_id')->unsigned()->nullable()->after('status_id');

            $table->foreign('visit_frequency_id')
                ->references('id')
                ->on('patient_visit_frequencies')
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
        Schema::table('patients', function (Blueprint $table) {
            $table->dropForeign(['visit_frequency_id']);
    
            $table->dropColumn('visit_frequency_id');
        });
    }
}
