<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAvailabilitySubtypeIdColumnToAvailabilityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('availabilities', function (Blueprint $table) {
            $table->unsignedSmallInteger('availability_subtype_id')->nullable()->after('availability_type_id')->comment('Displays the availability subtype if availability_type = Additional in the column');
            $table->text('comment')->nullable()->after('availability_subtype_id')->comment('Displays a comment on availability if the column availability_type = Additional and the column availability_subtype = Other');

            $table->foreign('availability_subtype_id')
                ->references('id')
                ->on('availability_subtypes')
                ->onUpdate('NO ACTION')
                ->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('availabilities', function (Blueprint $table) {
            $table->dropForeign(['availability_subtype_id']);
            $table->dropColumn('availability_subtype_id');
            $table->dropColumn('comment');
        });
    }
}
