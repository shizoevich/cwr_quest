<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\Resource;

class FaxResource extends Resource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'phone' => $this->phone,
            'is_read' => $this->is_read,
            'provider_id' => $this->provider_id,
            'file_name' => $this->file_name,
            'patient_id' => $this->patient_id,
            'patient_lead_id' => $this->patient_lead_id,
            'inquiry_id' =>  $this->formattedInquiryId(),
            'patient_status' => $this->formatPatientStatus(),
            'patient' => $this->formatPatientData(),
            'patient_lead' => $this->formatPatientLeadData(),
            'received_at' => $this->formattedReceivedAt(),
            'updated_at' => $this->formattedUpdatedAt(),
            'extensionId' => $this->extensionId,
            'type' => $this->type,
            'priority' => $this->priority,
            'direction' => $this->direction,
            'availability' => $this->availability,
            'subject' => $this->subject,
            'messageStatus' => $this->messageStatus,
            'faxResolution' => $this->faxResolution,
            'faxPageCount' => $this->faxPageCount,
            'lastModifiedTime' => $this->lastModifiedTime,
            'status_id' => $this->status_id,
        ];
    }

    protected function formatPatientData()
    {
        if ($this->patient_id) {
            return $this->patient->full_name ?? null;
        }
        return null;
    }

    protected function formatPatientLeadData()
    {
        if ($this->patientLead) {
            return $this->patientLead->getFullName();
        }
        return null;
    }

    protected function formatPatientStatus()
    {
        if ($this->patient_id) {
            return $this->patient->status->status ?? null;
        }
        return null;
    }

    protected function formattedReceivedAt()
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $this->creationTime)->format('m:d:Y H:i:s');
    }

    protected function formattedUpdatedAt()
    {
        return $this->updated_at->format('m:d:Y H:i:s');
    }

    protected function formattedInquiryId()
    {
        if (!$this->patient_lead_id || $this->patient_id) {
            return null;
        }

        $inquirable = $this->patient_lead_id
            ? $this->patientLead
            : $this->patient;

        return optional($inquirable->activeInquiry()->first())->id;
    }
}
