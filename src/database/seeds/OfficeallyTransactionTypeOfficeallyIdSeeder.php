<?php

use App\Models\Officeally\OfficeallyTransactionType;
use Illuminate\Database\Seeder;

class OfficeallyTransactionTypeOfficeallyIdSeeder extends Seeder
{
    private $transactionTypes = [
        [
            'name' => 'Cash',
            'officeally_id' => 1,
        ],
        [
            'name' => 'Check',
            'officeally_id' => 2,
        ],
        [
            'name' => 'Credit Card',
            'officeally_id' => 3,
        ],
        [
            'name' => 'Electronic Payment',
            'officeally_id' => 4,
        ],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach($this->transactionTypes as $transactionType) {
            OfficeallyTransactionType::where('name', $transactionType['name'])
                ->update([
                    'officeally_id' => $transactionType['officeally_id'],
                ]);
        }
    }   
}
