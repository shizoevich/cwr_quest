<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProviderCommentMentionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('provider_comment_mentions', function (Blueprint $table) {
            $table->integer('provider_id');
            $table->integer('comment_id');
            $table->string('model', 191);
            $table->timestamp('readed_at')->nullable();
            $table->timestamps();

            $table->primary(['provider_id', 'comment_id', 'model']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('provider_comment_mentions');
    }
}
