<?php

namespace App\Helpers\Constant;

class LoggerConst
{
    //fax came
    public const FAX_CAME = 'Fax came';
    //fax is uploaded to our system
    public const FAX_UPLOADED = 'Fax is uploaded to our system';
    //fax viewing
    public const FAX_VIEWING = 'Fax viewing';
    //fax downloaded
    public const FAX_DOWNLOADED = 'Fax downloaded';
    //fax comment
    public const FAX_COMMENT= 'Fax comment';
    //fax marked as read
    public const FAX_MARKED_AS_READ = 'Fax marked as read';
    //fax is marked as not read
    public const FAX_MARKED_AS_UNREAD  = 'Fax is marked as unread';
    //fax is attached to the patient (to whom exactly)
    public const FAX_ATTACHED_TO_PATIENT = 'Fax is attached to the patient';
    //fax is attached to the patient lead (to whom exactly)
    public const FAX_ATTACHED_TO_PATIENT_LEAD = 'Fax is attached to the patient lead';
    //fax is deattached from the patient (from whom exactly)
    public const FAX_DETACHED_FROM_PATIENT  = 'Fax is deattached from the patient';
    //fax is deattached from the patient lead (from whom exactly)
    public const FAX_DETACHED_FROM_PATIENT_LEAD  = 'Fax is deattached from the patient lead';
}
