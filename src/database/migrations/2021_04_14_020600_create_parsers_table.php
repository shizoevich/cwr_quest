<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parsers', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('service', [
                \App\Models\Parser::SERVICE_OFFICEALLY,
                \App\Models\Parser::SERVICE_TRIDIUUM,
            ]);
            $table->string('name', 64);
            $table->string('title');
            $table->text('description');
            $table->unsignedTinyInteger('status')->default(0);
            $table->boolean('allow_manual_start')->default(true);
            $table->timestamp('started_at')->nullable();
            
            $table->unique(['service', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('parsers');
    }
}
