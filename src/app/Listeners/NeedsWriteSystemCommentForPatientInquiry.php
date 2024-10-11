<?php

namespace App\Listeners;

use App\Models\Patient\Inquiry\PatientInquiry;
use App\Models\Patient\Inquiry\PatientInquiryComment;
use App\Repositories\NewPatientsCRM\PatientInquiry\PatientInquiryRepositoryInterface;

class NeedsWriteSystemCommentForPatientInquiry
{
    private $patientInquiryRepository;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(PatientInquiryRepositoryInterface $patientInquiryRepository)
    {
        $this->patientInquiryRepository = $patientInquiryRepository;
    }

    /**
     * Handle the event.
     *
     * @param \App\Events\NeedsWriteSystemCommentForPatientInquiry $event
     *
     * @return void
     */
    public function handle(\App\Events\NeedsWriteSystemCommentForPatientInquiry $event)
    {
        $inquiry = PatientInquiry::find($event->getInquiryId());

        $this->patientInquiryRepository->createComment(
            $inquiry,
            [
                'comment' => $event->getComment(),
                'admin_id' => $event->getAdminId(),
            ],
            true
        );
    }
}
