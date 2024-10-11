<?php

use App\Models\Patient\Inquiry\PatientInquiry;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStageChangedAtToPatientInquiriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_inquiries', function (Blueprint $table) {
            $table->timestamp('stage_changed_at')->nullable()->after('stage_id');
        });

        PatientInquiry::query()
            ->withTrashed()
            ->each(function ($inquiry) {
                $inquiry->update([
                    'stage_changed_at' => $inquiry->updated_at,
                ]);
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_inquiries', function (Blueprint $table) {
            $table->dropColumn('stage_changed_at');
        });
    }
}
