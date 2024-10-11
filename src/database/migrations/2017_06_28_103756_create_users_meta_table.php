<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersMetaTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if (!Schema::hasTable('users_meta')) {
            Schema::create('users_meta', function (Blueprint $table) {
                $table->integer('user_id')->unsigned();
                $table->string('firstname', 255)->nullable();
                $table->string('lastname', 255)->nullable();
                $table->text('about')->nullable();
                $table->string('photo', 255)->nullable();
                $table->string('signature', 255)->nullable();
                $table->boolean('is_admin')->default(false);
                $table->timestamps();

                $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::drop('users_meta');
    }
}
