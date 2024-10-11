<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAdminReadedAtFieldToProviderCommentMentionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('provider_comment_mentions', function(Blueprint $table) {
            $table->timestamp('admin_readed_at')->nullable()->after('readed_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('provider_comment_mentions', function(Blueprint $table) {
            $table->dropColumn('admin_readed_at');
        });
    }
}
