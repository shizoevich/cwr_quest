<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddExternalIdFieldToOfficesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('offices', function(Blueprint $table) {
            $table->dropUnique('office_UNIQUE');
            $table->string('external_id')->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('offices', function(Blueprint $table) {
            $table->dropColumn('external_id');
            $table->unique('office', 'office_UNIQUE');
        });
    }
}
