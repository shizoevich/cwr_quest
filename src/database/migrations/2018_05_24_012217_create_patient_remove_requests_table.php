<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientRemoveRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_remove_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('provider_id')->index();
            $table->integer('patient_id')->index();
            $table->string('reason');
            $table->integer('approver_id')->unsigned()
                ->nullable()
                ->description('UserID who accept or cancel request');
            $table->smallInteger('status')->default(0)
                ->description('New - 0, Accepted - 1, Canceled - 2');
            $table->timestamp('approved_at')->nullable();

            $table->foreign('provider_id')
                ->references('id')
                ->on('providers')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreign('patient_id')
                ->references('id')
                ->on('patients')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreign('approver_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('patient_remove_requests');
    }
}
