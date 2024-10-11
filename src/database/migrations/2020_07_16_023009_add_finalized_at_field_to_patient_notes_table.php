<?php

use App\PatientNote;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFinalizedAtFieldToPatientNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_notes', function(Blueprint $table) {
            $table->timestamp('finalized_at')->nullable()->after('is_finalized');
        });
        \DB::table('patient_notes')
            ->where('is_finalized', 1)
            ->update(['finalized_at' => \DB::raw('updated_at')]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_notes', function(Blueprint $table) {
            $table->dropColumn('finalized_at');
        });
    }
}
