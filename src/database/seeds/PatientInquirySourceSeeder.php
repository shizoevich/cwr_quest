<?php

use App\Models\Patient\Inquiry\PatientInquiryChannel;
use App\Models\Patient\Inquiry\PatientInquirySource;
use Illuminate\Database\Seeder;

class PatientInquirySourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $sources = [
            ['name' => 'Google', 'channel_name' => 'Organic Search'],
            ['name' => 'Yandex', 'channel_name' => 'Organic Search'],
            ['name' => 'Friend', 'channel_name' => 'Referral'],
            ['name' => 'Family', 'channel_name' => 'Referral'],
            ['name' => 'Doctor', 'channel_name' => 'Referral'],
            ['name' => 'Insurance comp', 'channel_name' => 'Referral'],
            ['name' => 'Client', 'channel_name' => 'Referral'],
            ['name' => 'Landing page', 'channel_name' => 'Website'],
            ['name' => 'Chat', 'channel_name' => 'Website'],
            ['name' => 'Google', 'channel_name' => 'PPC'],
            ['name' => 'LinkedIn', 'channel_name' => 'PPC'],
            ['name' => 'Subscriptions', 'channel_name' => 'Email'],
            ['name' => 'Cold base', 'channel_name' => 'Email'],
            ['name' => 'Youtube', 'channel_name' => 'Social Media'],
            ['name' => 'LinkedIn', 'channel_name' => 'Social Media'],
            ['name' => 'Facebook', 'channel_name' => 'Social Media'],
            ['name' => 'Twitter', 'channel_name' => 'Social Media'],
            ['name' => 'Online', 'channel_name' => 'Events'],
            ['name' => 'Offline', 'channel_name' => 'Events'],
            ['name' => 'Podcast', 'channel_name' => 'Events'],
            ['name' => 'Ebook', 'channel_name' => 'Content'],
            ['name' => 'Playbook', 'channel_name' => 'Content'],
            ['name' => 'Whitepaper', 'channel_name' => 'Content']
        ];
        foreach ($sources as $source) {
            $channel = PatientInquiryChannel::firstOrCreate(['name' => $source['channel_name']]);
            PatientInquirySource::create(['name' => $source['name'], 'channel_id' => $channel->id]);
        }
    }
}
