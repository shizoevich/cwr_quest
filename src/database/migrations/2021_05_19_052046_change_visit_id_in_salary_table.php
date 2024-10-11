<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeVisitIdInSalaryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('salary', function(Blueprint $table) {
            $table->unsignedInteger('visit_id')->nullable()->change();
            $table->string('notes')->nullable()->after('date');
            $table->text('additional_data')->nullable()->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('salary', function(Blueprint $table) {
            $table->unsignedInteger('visit_id')->change();
            $table->dropColumn(['notes', 'additional_data']);
        });
    }
}
