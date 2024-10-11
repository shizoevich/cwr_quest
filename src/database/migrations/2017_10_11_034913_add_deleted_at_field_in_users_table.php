<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeletedAtFieldInUsersTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('users', function(Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('users_meta', function(Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('providers', function(Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('users', function(Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
        Schema::table('users_meta', function(Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
        Schema::table('providers', function(Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
    }
}
