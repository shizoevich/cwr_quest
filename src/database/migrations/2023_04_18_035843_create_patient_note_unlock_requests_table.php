<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientNoteUnlockRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_note_unlock_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('provider_id');
            $table->integer('patient_note_id');
            $table->string('reason');
            $table->unsignedInteger('approver_id')->nullable()->default(null);
            $table->string('approver_comment')->nullable()->default(null);
            $table->smallInteger('status')->default(0)
                ->description('New - 0, Accepted - 1, Declined - 2, Canceled by therapist - 3');
            $table->timestamp('approved_at')->nullable()->default(null);
            $table->timestamps();

            $table->foreign('provider_id')
                ->references('id')
                ->on('providers')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');
            $table->foreign('patient_note_id')
                ->references('id')
                ->on('patient_notes')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');
            $table->foreign('approver_id')
                ->references('id')
                ->on('users')
                ->onDelete('SET NULL')
                ->onUpdate('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('patient_note_unlock_requests');
    }
}
