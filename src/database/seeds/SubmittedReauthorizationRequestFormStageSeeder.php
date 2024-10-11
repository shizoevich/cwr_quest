<?php

use App\Models\SubmittedReauthorizationRequestFormStage;
use Illuminate\Database\Seeder;

class SubmittedReauthorizationRequestFormStageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $stages = [
            'Ready to send',
            'Sent',
            'Approval received',
            'Rejected',
            'Edit required',
            'Auth. updated',
            'Archived',
            'Other',
            'Processing',
        ];

        foreach ($stages as $stage) {
            SubmittedReauthorizationRequestFormStage::firstOrCreate(['name' => $stage]);
        }
    }
}
