<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProviderCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('provider_comments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('provider_id')->index();
            $table->integer('admin_id')->unsigned();
            $table->text('comment')->nullable();;
            $table->string('original_file_name')->nullable();
            $table->string('aws_file_name')->nullable();
            $table->timestamps();

            $table->foreign('provider_id')
                ->references('id')
                ->on('providers')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');

            $table->foreign('admin_id')
                ->references('user_id')
                ->on('users_meta')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('provider_comments');
    }
}
