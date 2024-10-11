<?php

namespace App\Repositories\NewPatientsCRM\PatientInquirySource;

use App\Models\Patient\Inquiry\PatientInquiryChannel;
use App\Models\Patient\Inquiry\PatientInquirySource;
use Illuminate\Support\Collection;

class PatientInquirySourceRepository implements PatientInquirySourceRepositoryInterface
{
    public function getAll(): Collection
    {
        return PatientInquirySource::with('channel')->get();
    }

    public function createSource(array $data): PatientInquirySource
    {
        $source = PatientInquirySource::firstOrCreate([
            'name' => $data['name'],
            'channel_id' => PatientInquiryChannel::getOtherId()
        ]);
    
        $source->load('channel');
    
        return $source;
    }
}