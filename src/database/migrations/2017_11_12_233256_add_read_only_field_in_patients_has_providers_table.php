<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReadOnlyFieldInPatientsHasProvidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patients_has_providers', function(Blueprint $table) {
            $table->boolean('chart_read_only')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patients_has_providers', function(Blueprint $table) {
            $table->dropColumn('chart_read_only');
        });
    }
}
