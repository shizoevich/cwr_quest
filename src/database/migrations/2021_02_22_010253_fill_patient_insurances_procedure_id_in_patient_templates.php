<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FillPatientInsurancesProcedureIdInPatientTemplates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedures = DB::table('patient_insurances_procedures')->get();

        DB::table('patient_templates')->chunkById(500, function ($templates) use ($procedures) {
            foreach ($templates as $template) {
                $procedure = $procedures->where('code', $template->cpt)->first();

                if (!empty($procedure)) {
                    DB::table('patient_templates')->where('id', $template->id)->update([
                        'patient_insurances_procedure_id' => $procedure->id
                    ]);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
