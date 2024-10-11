<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToProvidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->string('first_name', 255)->nullable()->after('id');
			$table->string('last_name', 255)->nullable()->after('first_name');
			$table->string('middle_initial', 45)->nullable()->after('last_name');
            $table->string('taxonomy_code', 45)->nullable()->after('individual_npi');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->dropColumn('first_name');
            $table->dropColumn('last_name');
            $table->dropColumn('middle_initial');
            $table->dropColumn('taxonomy_code');
        });
    }
}
