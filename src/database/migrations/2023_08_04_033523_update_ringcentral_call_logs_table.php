<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateRingcentralCallLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ringcentral_call_logs', function (Blueprint $table) {
            $table->unsignedInteger('appointment_id')->nullable()->comment('Old morph column id')->change();
            $table->string('appointment_type', 64)->nullable()->comment('Old morph column type')->change();

            $table->unsignedInteger('call_subject_id')->nullable()->after('appointment_type')->comment('New morph column id');
            $table->string('call_subject_type')->nullable()->after('call_subject_id')->comment('New morph column type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ringcentral_call_logs', function (Blueprint $table) {
            $table->unsignedInteger('appointment_id')->change();
            $table->string('appointment_type')->change();

            $table->dropColumn('call_subject_id');
            $table->dropColumn('call_subject_type');
        });
    }
}
