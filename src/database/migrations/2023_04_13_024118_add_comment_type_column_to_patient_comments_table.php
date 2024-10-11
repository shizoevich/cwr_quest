<?php

use App\PatientComment;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCommentTypeColumnToPatientCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_comments', function (Blueprint $table) {
            $table->tinyInteger('comment_type')
                ->nullable()
                ->default(PatientComment::DEFAULT_COMMENT_TYPE)
                ->after('comment')
                ->comment('Used to distinguish between default and cancellation types of comments');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_comments', function (Blueprint $table) {
            $table->dropColumn('comment_type');
        });
    }
}
