<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropSitesColumnsFromKaiserAppointments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('kaiser_appointments', function (Blueprint $table) {
            $table->dropColumn('tridiuum_site_id');
            $table->dropColumn('tridiuum_site_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('kaiser_appointments', function (Blueprint $table) {
            $table->string('tridiuum_site_id', 128)->nullable()->after('provider_id');
            $table->string('tridiuum_site_name')->nullable()->after('tridiuum_site_id');
        });
    }
}
