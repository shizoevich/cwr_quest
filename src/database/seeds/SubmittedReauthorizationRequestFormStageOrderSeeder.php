<?php

use App\Models\SubmittedReauthorizationRequestFormStage;
use Illuminate\Database\Seeder;

class SubmittedReauthorizationRequestFormStageOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $stages = [
            [
                'name' => 'Ready to send',
                'order' => 1,
            ],
            [
                'name' => 'Sent',
                'order' => 2,
            ],
            [
                'name' => 'Processing',
                'order' => 3,
            ],
            [
                'name' => 'Approval received',
                'order' => 4,
            ],
            [
                'name' => 'Rejected',
                'order' => 5,
            ],
            [
                'name' => 'Edit required',
                'order' => 6,
            ],
            [
                'name' => 'Auth. updated',
                'order' => 7,
            ],
            [
                'name' => 'Archived',
                'order' => 8,
            ],
            [
                'name' => 'Other',
                'order' => 9,
            ],
        ];

        foreach ($stages as $stage) {
            SubmittedReauthorizationRequestFormStage::query()
                ->where('name', $stage['name'])
                ->update([
                    'order' => $stage['order'],
                ]);
        }
    }
}
