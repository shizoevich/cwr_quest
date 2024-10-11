<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientLeadCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_lead_comments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('patient_lead_id');
            $table->text('comment');
            $table->unsignedInteger('admin_id')->nullable()->default(null);
            $table->boolean('is_system_comment');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('admin_id')
                ->references('user_id')
                ->on('users_meta')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('patient_lead_id')
                ->references('id')
                ->on('patient_leads')
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
        Schema::dropIfExists('patient_lead_comments');
    }
}
