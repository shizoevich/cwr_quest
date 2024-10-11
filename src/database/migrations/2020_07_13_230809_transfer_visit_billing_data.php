<?php

use App\PatientVisit;
use Illuminate\Database\Migrations\Migration;

class TransferVisitBillingData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::statement("
            UPDATE `patient_visits`
            JOIN `patient_visit_billings` ON `patient_visit_billings`.`visit_id` = `patient_visits`.`id`
            SET
                `patient_visits`.`pos` = IF(`patient_visit_billings`.`pos` = 2, '02', `patient_visit_billings`.`pos`),
                `patient_visits`.`procedure_id` = `patient_visit_billings`.`insurance_procedure_id`
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        PatientVisit::query()->update([
            'procedure_id' => null,
            'pos'          => null,
        ]);
    }
}
