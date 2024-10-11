<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubmittedReauthorizationRequestFormStageChangeHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('submitted_reauthorization_request_form_stage_change_history', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('form_id');
            $table->unsignedInteger('old_stage_id');
            $table->unsignedInteger('new_stage_id');
            $table->unsignedInteger('user_id')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('form_id', 'srrf_stage_change_history_form_id')
                ->references('id')
                ->on('submitted_reauthorization_request_forms')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');

            $table->foreign('old_stage_id', 'srrf_stage_change_history_old_stage_id')
                ->references('id')
                ->on('submitted_reauthorization_request_form_stages')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');

            $table->foreign('new_stage_id', 'srrf_stage_change_history_new_stage_id')
                ->references('id')
                ->on('submitted_reauthorization_request_form_stages')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');

            $table->foreign('user_id', 'srrf_stage_change_history_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('submitted_reauthorization_request_form_stage_change_history');
    }
}
