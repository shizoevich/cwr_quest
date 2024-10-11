<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubmittedReauthorizationRequestFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('submitted_reauthorization_request_forms', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('document_id')->index();
            $table->string('document_type');
            $table->unsignedInteger('stage_id');
            $table->text('comment')->nullable();
            $table->timestamp('stage_changed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('stage_id')
                ->references('id')
                ->on('submitted_reauthorization_request_form_stages')
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
        Schema::dropIfExists('submitted_reauthorization_request_forms');
    }
}
