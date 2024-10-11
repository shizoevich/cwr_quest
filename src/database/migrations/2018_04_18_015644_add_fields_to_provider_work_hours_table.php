<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToProviderWorkHoursTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('provider_work_hours', function (Blueprint $table) {
            $table->timestamp('start_date')
                ->nullable()
                ->after('length');
            $table->timestamp('end_date')
                ->nullable()
                ->after('start_date');
            $table->integer('repeat')
                ->default(0)
                ->after('end_date');
            $table->integer('parent_id')
                ->nullable()
                ->after('repeat');
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
        Schema::table('provider_work_hours', function (Blueprint $table) {
            $table->dropColumn('start_date');
            $table->dropColumn('end_date');
            $table->dropColumn('repeat');
            $table->dropColumn('parent_id');
            $table->dropSoftDeletes();
        });
    }
}
