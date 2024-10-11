<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientInquiriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_inquiries', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('inquirable_id')->index();
            $table->string('inquirable_type');
            $table->unsignedInteger('stage_id');
            $table->unsignedInteger('registration_method_id');
            $table->unsignedInteger('source_id');
            $table->string('marketing_activity')->nullable();
            $table->string('from_bdr')->nullable();
            $table->unsignedInteger('admin_id')->nullable();
            $table->boolean('is_returning')->default(0);
            $table->boolean('is_archived')->default(0);
            $table->timestamp('closed_at')->nullable()->default(null);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('stage_id')
                ->references('id')
                ->on('patient_inquiry_stages')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('registration_method_id')
                ->references('id')
                ->on('patient_inquiry_registration_methods')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('source_id')
                ->references('id')
                ->on('patient_inquiry_sources')
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
        Schema::dropIfExists('patient_inquiries');
    }
}
