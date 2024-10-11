<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientDocumentTypesTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('patient_document_types', function(Blueprint $table) {
            $table->increments('id');
            $table->string('type', 255);
            $table->integer('parent')->default(0);
            $table->boolean('clickable');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('patient_document_types');
    }
}
