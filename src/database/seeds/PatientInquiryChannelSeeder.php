<?php

use App\Models\Patient\Inquiry\PatientInquiryChannel;
use Illuminate\Database\Seeder;

class PatientInquiryChannelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $channels = [
            "Organic Search",
            "Referral",
            "Website",
            "PPC",
            "Email",
            "Social Media",
            "Events",
            "Partnership",
            "Content",
            "Other"
        ];

        foreach($channels as $channel) {
            PatientInquiryChannel::create(['name' => $channel]);
        }
    }
}
