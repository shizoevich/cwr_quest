<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToSystemMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('system_messages', function(Blueprint $table) {
            $table->timestamp('expires_at')->nullable()->after('text');
            $table->string('page')->nullable()->after('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('system_messages', function(Blueprint $table) {
            $table->dropColumn(['expires_at', 'page']);
        });
    }
}
