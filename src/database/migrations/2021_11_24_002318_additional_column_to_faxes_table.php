<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AdditionalColumnToFaxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('faxes', function (Blueprint $table) {
            $table->integer('provider_id')->nullable()->unsigned()->after('is_read');
            $table->integer('patient_id')->nullable()->unsigned()->after('provider_id');
            $table->string('uri', 1000)->nullable();
            $table->bigInteger('extensionId')->nullable();
            $table->string('phoneNumber', 75)->nullable();
            $table->string('type', 75)->nullable();
            $table->timestamp('creationTime')->nullable();
            $table->string('readStatus', 75)->nullable();
            $table->string('priority', 75)->nullable();
            $table->string('direction', 75)->nullable();
            $table->string('availability', 75)->nullable();
            $table->string('subject', 75)->nullable();
            $table->string('messageStatus', 75)->nullable();
            $table->string('faxResolution', 75)->nullable();
            $table->bigInteger('faxPageCount')->nullable();
            $table->timestamp('lastModifiedTime')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('faxes', function (Blueprint $table) {
            $table->dropColumn('provider_id');
            $table->dropColumn('patient_id');
            $table->dropColumn('uri');
            $table->dropColumn('extensionId');
            $table->dropColumn('phoneNumber');
            $table->dropColumn('type');
            $table->dropColumn('creationTime');
            $table->dropColumn('readStatus');
            $table->dropColumn('priority');
            $table->dropColumn('direction');
            $table->dropColumn('availability');
            $table->dropColumn('subject');
            $table->dropColumn('messageStatus');
            $table->dropColumn('faxResolution');
            $table->dropColumn('faxPageCount');
            $table->dropColumn('lastModifiedTime');
        });
    }
}
