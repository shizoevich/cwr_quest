<?php

use App\Models\Officeally\OfficeallyTransactionPurpose;
use Illuminate\Database\Seeder;

class OfficeallyTransactionPurposeTableSeeder extends Seeder
{
    private $purposes = [
        [
            'name' => OfficeallyTransactionPurpose::PURPOSE_COPAY,
            'description' => 'Copay'
        ],
        [
            'name' => OfficeallyTransactionPurpose::PURPOSE_DEDUCTIBLE,
            'description' => 'Deductible'
        ],
        [
            'name' => OfficeallyTransactionPurpose::PURPOSE_SELF_PAY,
            'description' => 'Self-pay'
        ],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->purposes as $purpose) {
            OfficeallyTransactionPurpose::updateOrCreate(['name' => $purpose['name']], $purpose);
        }
    }
}
