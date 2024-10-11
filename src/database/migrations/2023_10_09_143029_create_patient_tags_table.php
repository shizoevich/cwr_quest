<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_tags', function (Blueprint $table) {
            $table->increments('id');
            $table->string('tag');
            $table->string('hex_text_color')->nullable();
            $table->string('hex_background_color')->nullable();
            $table->boolean('is_system')->default(0);
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('created_by')
                ->references('id')
                ->on('users');
        });

        Schema::create('patient_has_tags', function (Blueprint $table) {
            $table->integer('patient_id');
            $table->unsignedInteger('tag_id');

            $table->foreign('patient_id')
                ->references('id')
                ->on('patients')
                ->onDelete('cascade');
            $table->foreign('tag_id')
                ->references('id')
                ->on('patient_tags')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('patient_has_tags');
        Schema::dropIfExists('patient_tags');
    }
}
