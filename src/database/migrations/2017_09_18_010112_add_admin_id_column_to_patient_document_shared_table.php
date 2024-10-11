<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAdminIdColumnToPatientDocumentSharedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_document_shared',
            function (Blueprint $table) {
                $table->integer('provider_id')->nullable()->change();
                $table->integer('admin_id')->nullable()->unsigned()->after('provider_id');

                $table->foreign('admin_id')
                    ->references('user_id')
                    ->on('users_meta')
                    ->onDelete('cascade')
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
        Schema::table('patient_document_shared',
            function (Blueprint $table) {
                $table->integer('provider_id')->change();
                $table->dropForeign(['admin_id']);
                $table->dropColumn(['admin_id']);
            });
    }
}
