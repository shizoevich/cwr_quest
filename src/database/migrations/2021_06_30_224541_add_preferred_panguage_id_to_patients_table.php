<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPreferredPanguageIdToPatientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patients', function(Blueprint $table) {
            $table->unsignedInteger('preferred_language_id')->nullable()->after('primary_provider_id');
            $table->foreign('preferred_language_id')
                ->references('id')
                ->on('languages')
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
        Schema::table('patients', function(Blueprint $table) {
            $table->dropForeign(['preferred_language_id']);
            $table->dropColumn('preferred_language_id');
        });
    }
}
