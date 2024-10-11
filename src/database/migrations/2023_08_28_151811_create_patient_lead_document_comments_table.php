<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientLeadDocumentCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_lead_document_comments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('patient_lead_documents_id')->unsigned();
            $table->integer('admin_id')->unsigned();
            $table->string('content', 255);
            $table->boolean('is_system_comment')->default(0);
            $table->timestamps();

            $table->foreign('patient_lead_documents_id')
                ->references('id')
                ->on('patient_lead_documents')
                ->onDelete('cascade')
                ->onUpdate('cascade');

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
        Schema::dropIfExists('patient_lead_document_comments');
    }
}
