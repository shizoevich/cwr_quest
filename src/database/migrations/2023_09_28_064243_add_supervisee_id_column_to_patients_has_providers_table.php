<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSuperviseeIdColumnToPatientsHasProvidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patients_has_providers', function (Blueprint $table) {
            $table->integer('supervisee_id')->key()->nullable()->comment('Displays the provider id if the connection was created when the supervisee was attached to the supervisor');

            $table->foreign('supervisee_id')
                ->references('id')
                ->on('providers')
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
        Schema::table('patients_has_providers', function (Blueprint $table) {
            $table->dropForeign(['supervisee_id']);
            $table->dropColumn('supervisee_id');
        });
    }
}
