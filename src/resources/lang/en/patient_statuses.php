<?php

return [
    'new_to_active' => 'Patient status was changed to <span class="patient-status-text" style="color:#02a756;">ACTIVE</span>.<br>At least one (1) visit was created for a patient with <span class="patient-status-text">NEW</span> status.',
    'new_to_lost' => 'Patient status was changed to <span class="patient-status-text" style="color:#fb0007;">LOST</span>.<br>There have been no visits created for thirty (30) days or more since the status <span class="patient-status-text">NEW</span> has been assigned.',
    'to_new' => 'Patient status <span class="patient-status-text">NEW</span> was assigned.<br>New patient was added into the system and the status <span class="patient-status-text">NEW</span> was assigned.',

    'inactive_to_active' => 'Patient status was changed to <span class="patient-status-text" style="color:#02a756;">ACTIVE</span>.<br>At least one (1) visit was created for a patient with <span class="patient-status-text" style="color:#d09b00;">INACTIVE</span> status.',
    'inactive_to_lost' => 'Patient status was changed to <span class="patient-status-text" style="color:#fb0007;">LOST</span>.<br>There have been no visits created for :inactive_to_lost_days_text (:inactive_to_lost_days_number) days or more since the status <span class="patient-status-text" style="color:#d09b00;">INACTIVE</span> has been assigned.',

    'active_to_inactive' => "Patient status was changed to <span class=\"patient-status-text\" style=\"color:#d09b00;\">INACTIVE</span>.<br>There have been no visits created for :active_to_inactive_days_text (:active_to_inactive_days_number) days or more."
        . "<br><br><b>IMPORTANT NOTICE TO ADMIN STAFF AND THERAPISTS!!!</b><br><b>PLEASE READ AND FOLLOW INSTRUCTIONS OUTLINED BELOW:</b><br><br>"
        . "<span class=\"patient-status-text\" style=\"color:#d09b00;\">INACTIVE</span> status triggers some actions to be performed automatically by the system, and further <b><u>requires actions to be taken by admin staff and therapists involved in this patient&#039;s treatment</u></b>."
        . " Overall, patients with <span class=\"patient-status-text\" style=\"color:#d09b00;\">INACTIVE</span> status must be monitored closely for the following reasons:<br><br>"
        . "<ul>"
        . "<li>to ensure patient&#039;s wellbeing and offer the highest level of care with a personal touch by determining the reasons for treatment interruption, discussing a possibility of switching therapists, or making any other changes</li>"
        . "<li>to protect the company and its contractors from potential liabilities by detecting and resolving issues which can potentially cause a legal dispute</li>"
        . "<li>to provide a great working environment and opportunities for staff and contractors through better management of administrative and billing procedures, teamwork and overall growths as a group provider on the competitive marketplace.</li>"
        . "</ul>"
        . "<br>"
        . "Following actions will be performed automatically:<br><br>"
        . "<ul>"
        . "<li>As soon as the <span class=\"patient-status-text\" style=\"color:#d09b00;\">INACTIVE</span> status is assigned, <b><u>all previously scheduled appointments for future sessions will be deleted in Office Ally</u></b>. This action is performed once by the system to ensure accuracy in scheduling. It is NOT RECOMMENDED to set reoccurring appointments, until the patient demonstrates intentions to continue treatment by showing up for at least one (1) appointment. <b><u>Until this happens, please set one appointment at a time and make sure to log all communications with the patient in the chart.</u></b></li>"
        . "</ul>"
        . "<br>"
        . "Therapist&#039;s responsibility to avoid <span class=\"patient-status-text\" style=\"color:#fb0007;\">LOST</span> status assigned to patients:<br><br>"
        . "<ul>"
        . "<li>The therapists MUST contact the patient with <span class=\"patient-status-text\" style=\"color:#d09b00;\">INACTIVE</span> status at least three (3) times within thirty (30) days since <span class=\"patient-status-text\" style=\"color:#d09b00;\">INACTIVE</span> status has been assigned.</li>"
        . "<li>The therapist MUST record all attempts to contact the patient in the comment section of the Electronic Chart.</li>"
        . "<li>The therapist is responsible for filing a Discharge Form within :inactive_to_lost_days_text (:inactive_to_lost_days_number) days since <span class=\"patient-status-text\" style=\"color:#d09b00;\">INACTIVE</span> status has been assigned to prevent <span class=\"patient-status-text\" style=\"color:#fb0007;\">LOST</span> status assignment.</li>"
        . "<li>This patient&#039;s status will remain <span class=\"patient-status-text\" style=\"color:#d09b00;\">INACTIVE</span> and will only change to <span class=\"patient-status-text\" style=\"color:#02a756;\">ACTIVE</span> when at least one (1) visit is created in the next :active_to_inactive_days_text (:active_to_inactive_days_number) days.</li>"
        . "<li>If no visits created in the next :inactive_to_lost_days_text (:inactive_to_lost_days_number) days, the status will change to <span class=\"patient-status-text\" style=\"color:#fb0007;\">LOST</span></li>"
        . "</ul>"
        . "<br>"
        . "<b><u>ATTENTION THERAPISTS!!! A patient with <span class=\"patient-status-text\" style=\"color:#fb0007;\">LOST</span> status may negatively affect your rating as a contractor of CWR. Too many <span class=\"patient-status-text\" style=\"color:#fb0007;\">LOST</span> patients can potentially cause your contract with CWR to be terminated. You can avoid <span class=\"patient-status-text\" style=\"color:#fb0007;\">LOST</span> status to be assigned to the patient by filing a Discharge Form.</u></b>",

    'lost_to_new' => 'Lost -> New',
    'lost_to_active' => 'Patient status was changed to <span class="patient-status-text" style="color:#02a756;">ACTIVE</span>.<br>At least one (1) visit was created for a patient with <span class="patient-status-text" style="color:#fb0007;">LOST</span> status.',

    'archived_to_active' => 'Patient status was changed to <span class="patient-status-text" style="color:#02a756;">ACTIVE</span>.<br>Previously <span class="patient-status-text">ARCHIVED</span> patient scheduled an appointment.',

    'discharged_to_archived' => 'Patient status was changed to <span class="patient-status-text">ARCHIVED</span>.<br>There has been no activity for one (1) year since <span class="patient-status-text" style="color:#0000ff;">DISCHARGED</span> status was assigned.',
    'discharged_to_active' => 'Patient status was changed to <span class="patient-status-text" style="color:#02a756;">ACTIVE</span>.<br>Previously <span class="patient-status-text" style="color:#0000ff;">DISCHARGED</span> patient scheduled an appointment.',

    'to_discharged' => 'Patient status was changed to <span class="patient-status-text" style="color:#0000ff;">DISCHARGED</span>.<br>The discharge form was submitted by the therapist assigned to this patient.',
];
