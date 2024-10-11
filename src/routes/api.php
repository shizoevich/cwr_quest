<?php

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Patient\PatientTag;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::middleware(['admin-secretary'])->group(function () {
    /** POST   /ringcentral/faxes/patient-add-pdf  */
    Route::post('/ringcentral/faxes/patient-add-pdf', 'PatientController@uploadFile');
    /** POST   /ringcentral/faxes/patient-remove-pdf  */
    Route::post('/ringcentral/faxes/patient-remove-pdf', 'Dashboard\UsersController@deleteDocument');
});

Route::namespace('Api')->group(function () {
    Route::get('menu-api', 'MenuApiController@index')->middleware('admin-or-supervisor');

    Route::middleware(['admin'])->group(function () {
        Route::get('appointment-dirties', 'Appointment\AppointmentController@importantDirties');

        Route::get('offices', 'OfficeController@index');

        Route::put('patients/{patient}/providers', 'Patient\PatientController@updateAttachedProviders');

        Route::get('patients/{patient}', 'Patient\PatientController@show');

        Route::post('patients', 'Patient\PatientController@store');

        Route::patch('patients/{patient}', 'Patient\PatientController@update');

        Route::get('insurances', 'PatientInsuranceController@index');

        Route::get('insurances/all', 'PatientInsuranceController@getInsuranceList');

        Route::get('eligibility-payers', 'EligibilityPayerController@index');

        Route::get('insurance-procedures', 'PatientInsuranceProcedureController@index');

        Route::get('therapy-types', 'PatientTherapyTypeController@index');

        Route::get('providers', 'ProviderController@index');

        Route::get('providers/{with_trashed_provider}/patients', 'ProviderController@getPatients');

        Route::get('providers/{provider}/additional-compensation', 'Provider\SalaryController@additionalCompensation');

        Route::post('providers/{provider}/additional-compensation', 'Provider\SalaryController@storeAdditionalCompensation');

        Route::post('providers/{provider}/is-supervisor', 'SupervisingController@storeIsSupervisor');

        Route::post('providers/{provider}/attach-supervisor', 'SupervisingController@attachSupervisor');

        Route::get('dashboard/salary/timesheets', 'Provider\AdminSalaryTimesheetController@index');
        Route::post('dashboard/salary/timesheets/visits/{timesheet_visit}/accept', 'Provider\AdminSalaryTimesheetController@acceptVisit');
        Route::post('dashboard/salary/timesheets/visits/{timesheet_visit}/decline', 'Provider\AdminSalaryTimesheetController@declineVisit');
        Route::post('dashboard/salary/timesheets/late-cancellations/{timesheet_late_cancellation}/accept', 'Provider\AdminSalaryTimesheetController@acceptLateCancellation');
        Route::post('dashboard/salary/timesheets/late-cancellations/{timesheet_late_cancellation}/decline', 'Provider\AdminSalaryTimesheetController@declineLateCancellation');

        Route::post('dashboard/salary/timesheets/{salary_timesheet}', 'Provider\AdminSalaryTimesheetController@completeTimesheet');

        Route::get('dashboard/salary/timesheets/{salary_timesheet}', 'Provider\AdminSalaryTimesheetController@get');

        Route::post('dashboard/salary/timesheets/delete/{timesheet_late_cancellation}', 'Provider\AdminSalaryTimesheetController@deleteLateCancellationsFromTimeSheets');


        Route::get('/transfers', 'Patient\PatientTransferController@index');

        Route::post('/transfers/transfer-patient', 'Patient\PatientTransferController@transferPatient');

        Route::prefix('provider')->group(function () {
            Route::get('{provider}/comment', 'Provider\ProviderCommentController@index');
            Route::post('{provider}/comment', 'Provider\ProviderCommentController@store');
        });
    });

    Route::middleware(['admin-secretary'])->prefix('ringcentral')->group(static function () {
        /** GET   /ringcentral/faxes        */
        Route::get('faxes', 'Ringcentral\FaxController@index');
        /** POST   /ringcentral/faxes/read-status  */
        Route::post('faxes/read-status', 'Ringcentral\FaxController@updateReadStatus');
        /** POST   /ringcentral/faxes/unread-status  */
        Route::post('faxes/unread-status', 'Ringcentral\FaxController@updateUnreadStatus');
        /** GET   /ringcentral/sync-fax      */
        Route::get('sync-fax', 'Ringcentral\FaxController@faxSync');
        /** GET   /ringcentral/patients      */
        Route::get('patients', 'Ringcentral\PatientController@index');
        /** POST   /ringcentral/fax-attach      */
        Route::post('fax-attach', 'Ringcentral\PatientController@attach');
        /** POST   /ringcentral/fax-detach      */
        Route::post('fax-detach', 'Ringcentral\PatientController@dettach');
        /** GET   /ringcentral/fax-logs   */
        Route::get('fax-logs', 'Ringcentral\FaxLoggingController@index');
        /** GET   /ringcentral/fax-download     */
        Route::get('fax-download', 'Ringcentral\FaxController@download');
        /** GET   /ringcentral/fax-view     */
        Route::get('fax-view', 'Ringcentral\FaxController@faxView');
        /** POST  /ringcentral/logging     */
        Route::post('fax-logging', 'Ringcentral\FaxLoggingController@logging');
        /** GET   /ringcentral/account-numbers     */
        Route::get('account-numbers', 'Ringcentral\RingOutController@getAccountNumbers');
    });

    Route::middleware(['user-provider'])->group(function () {
        Route::put('/ringcentral/ring-out/{call_log}', 'Ringcentral\RingOutController@update');
        Route::delete('/ringcentral/ring-out/{call_log}', 'Ringcentral\RingOutController@destroy');

        Route::post('/ringcentral/appointments/ring-out', 'Ringcentral\RingOutController@storeForAppointment');
        Route::get('/ringcentral/appointments/{appointment}/ring-out', 'Ringcentral\RingOutController@getByAppointment');

        Route::post('/ringcentral/patients/ring-out', 'Ringcentral\RingOutController@storeForPatient');
        Route::get('/ringcentral/patients/{patient}/call-details', 'Ringcentral\RingOutController@getPatientCallDetails');

        Route::post('/ringcentral/patients/log-external-ring-out', 'Ringcentral\RingOutController@storeExternalLogForPatient');
        
        Route::get('salary/timesheet-confirmation', 'Provider\SalaryTimesheetController@checkConfirmation');

        Route::get('salary/timesheet/visits', 'Provider\SalaryTimesheetController@visits');
        Route::post('salary/timesheet/visits', 'Provider\SalaryTimesheetController@modifyVisits');

        Route::get('salary/timesheet/late-cancellations', 'Provider\SalaryTimesheetController@lateCancellations');
        Route::post('salary/timesheet/late-cancellations', 'Provider\SalaryTimesheetController@modifyLateCancellations');

        Route::get('salary/timesheet/supervisions', 'Provider\SalaryTimesheetController@supervisions');
        Route::post('salary/timesheet/supervisions', 'Provider\SalaryTimesheetController@modifySupervisions');

        Route::post('salary/timesheet/complete', 'Provider\SalaryTimesheetController@complete');

        Route::post('salary/timesheet-notification/mark-as-viewed', 'Provider\SalaryTimesheetController@markAsViewed');
        Route::post('salary/timesheet-notification/remind-later', 'Provider\SalaryTimesheetController@remindLater');

        Route::get('appointments', 'Appointment\AppointmentController@index');

        Route::get('patients', 'Patient\PatientController@index');

        Route::get('offices', 'OfficeController@index');

        Route::get('offices/{office}/rooms', 'OfficeRoomController@index');

        Route::get('appointments/available-statuses', 'Appointment\AppointmentController@availableStatuses');

        Route::delete('appointments/{appointment}', 'Appointment\AppointmentController@destroy')->middleware('admin');

        Route::post('appointments', 'Appointment\AppointmentController@store');

        Route::put('appointments/{appointment}', 'Appointment\AppointmentController@update');

        Route::get('states', 'Patient\PatientController@states');

        Route::get('appointments/{appointment}', 'Appointment\AppointmentController@show');

        /** @see \App\Http\Controllers\Api\Appointment\CheckValidPhoneController::check()  */
        Route::post('validate-phone', 'Appointment\CheckValidPhoneController@check');

        Route::get('availability/types', 'Availability\AvailabilityTypeController@index');
        Route::get('availability/subtypes', 'Availability\AvailabilityTypeController@showSubtypes');

        Route::get('/patients/{patient}/faxes', 'Patient\PatientController@getFaxes');
        Route::get('/leads/{patientLead}/faxes', 'NewPatientsCrm\PatientLeadController@getFaxes');

        Route::post('/leads/{patientLead}/attach-fax/{fax}', 'NewPatientsCrm\PatientLeadController@attachFax');
        Route::post('/leads/{patientLead}/detach-fax/{fax}', 'NewPatientsCrm\PatientLeadController@detachFax');

        Route::get('/transaction-purposes', 'OfficeAllyTransaction\OfficeAllyTransactionController@index'); 
    });

    Route::prefix('time')->group(function () {
        Route::get('current-time', 'DateTimeController@getTime');

        Route::get('current-timestamp', 'DateTimeController@getTimestamp');

        Route::get('current-date-and-time', 'DateTimeController@getDateAndTime');

        Route::get('now', 'DateTimeController@now');

        Route::get('date-parts', 'DateTimeController@dateParts');
    });

    Route::prefix('system')->group(function () {
        /** @see App\Http\Controllers\Api\System\PatientFormTypeController::index() */
        Route::get('patient-form-types', 'System\PatientFormTypeController@index');
        Route::get('languages', 'System\LanguageController@index');
        Route::get('therapist-custom-timesheet', 'ProviderController@isTherapistCustomTimesheet');
    });

    Route::prefix('public/patients')->group(function () {
        Route::get('shared-documents/{shared}', 'PatientForm\PatientDocumentRequestSharedDocumentController@checkHashExpired');
        Route::post('shared-documents/{shared}', 'PatientForm\PatientDocumentRequestSharedDocumentController@show');

        //put public patient routes here
        /** @see \App\Http\Controllers\Api\Patient\DocumentRequest\PatientDocumentRequestController::show() */
        Route::get(
            'document-requests/{document_request}',
            'Patient\DocumentRequest\PatientDocumentRequestController@show'
        );

        /** @see \App\Http\Controllers\Api\PatientForm\PatientController::search() */
        Route::get('search', 'PatientForm\PatientController@search');

        /** @see \App\Http\Controllers\Api\PatientForm\PatientController::show() */
        Route::get('{encrypted_patient}', 'PatientForm\PatientController@show');

        /** @see \App\Http\Controllers\Api\PatientForm\PatientFormController::storeNewPatientForm() */
        Route::post('{encrypted_patient}/new-patient', 'PatientForm\PatientFormController@storeNewPatientForm');

        /** @see \App\Http\Controllers\Api\PatientForm\PatientFormController::storeConfidentalInformationForm() */
        Route::post(
            '{encrypted_patient}/confidential-information',
            'PatientForm\PatientFormController@storeConfidentalInformationForm'
        );

        /** @see \App\Http\Controllers\Api\PatientForm\PatientFormController::storeTelehealthForm() */
        Route::post('{encrypted_patient}/telehealth', 'PatientForm\PatientFormController@storeTelehealthForm');

        /** @see \App\Http\Controllers\Api\PatientForm\PatientFormController::storeSupportingDocumentsForm() */
        Route::post(
            '{encrypted_patient}/supporting-documents',
            'PatientForm\PatientFormController@storeSupportingDocumentsForm'
        );

        /** @see \App\Http\Controllers\Api\PatientForm\PatientFormController::storePaymentForServiceForm() */
        Route::post(
            '{encrypted_patient}/payment-for-service',
            'PatientForm\PatientFormController@storePaymentForServiceForm'
        );

        /** @see \App\Http\Controllers\Api\Patient\DocumentRequest\PatientDocumentRequestController::getDocumentsForItem() */
        Route::get(
            'document-requests/{document_request}/{document_request_item}',
            'Patient\DocumentRequest\PatientDocumentRequestController@getDocumentsForItem'
        );
    });

    Route::prefix('patients')->group(function () {
        Route::group(['middleware' => ['user-provider', 'profile-completed']], function () {
            Route::post('{patient}/appointments/{appointment}/pay', 'Appointment\CompleteAppointmentController@pay');

            Route::get('{patient}/telehealth-appointments', 'Appointment\TelehealthAppointmentController@index');

            Route::get('{patient}/appointments/{appointment}', 'Appointment\CompleteAppointmentController@show'); 

            /** @see \App\Http\Controllers\Api\Patient\Document\PatientDocumentController::loadLucetInitialAssessment() */
            Route::post('{patient}/load-tridiuum-initial-assessment', 'Patient\Document\PatientDocumentController@loadTridiuumInitialAssessment');

            Route::get('{patient}/credit-cards', 'Patient\PatientCreditCardController@index');

            Route::get('{patient}/catalog-items', 'Patient\PatientCreditCardController@getCatalogItems');

            Route::get('{patient}/chargeable-appointments', 'Patient\PatientCreditCardController@getChargeableAppointments');

            Route::post('{patient}/charge', 'Patient\PatientCreditCardController@charge');

            Route::put('{patient}/diagnoses', 'Patient\PatientDiagnoseController@update');

            Route::post('{patient}/video-session', 'Patient\PatientVideoSessionController@store');

            Route::get('{patient}/video-session/{videoSession}', 'Patient\PatientVideoSessionController@show');

            // @todo change logic when "upheal" integration will be finished
            Route::post('{patient}/upheal-video-session', 'Patient\PatientVideoSessionController@storeUphealVideoSession');

            Route::get('{patient}/form-types', 'System\PatientFormTypeController@indexByPatient');
            /** @see \App\Http\Controllers\Api\Patient\DocumentRequest\PatientDocumentRequestController::index() */
            Route::get('{patient}/forms', 'Patient\DocumentRequest\PatientDocumentRequestController@index'); 

            Route::post('{patient}/forms/send', 'Patient\DocumentRequest\PatientDocumentRequestController@send');

            /** @see \App\Http\Controllers\Api\Patient\DocumentRequest\PatientDocumentRequestController::getPatientFormsCount() */
            Route::get('{patient}/forms/count', 'Patient\DocumentRequest\PatientDocumentRequestController@getPatientFormsCount'); 

            /** @see \App\Http\Controllers\Api\PatientForm\PatientFormController::approve() */
            Route::post('forms/{patient_form}/approve', 'PatientForm\PatientFormController@approve');

            /** @see \App\Http\Controllers\Api\PatientForm\PatientFormController::decline() */
            Route::post('forms/{patient_form}/decline', 'PatientForm\PatientFormController@decline');

            /** @see \App\Http\Controllers\Api\Patient\Document\PatientDocumentController::download() */
            Route::get('{patient}/documents/download', 'Patient\Document\PatientDocumentController@download');

            /** @see \App\Http\Controllers\Api\Patient\Document\PatientDocZipGeneratorController::generatePatientDocZip() */
            Route::get('{patient}/documents-zip/generate', 'Patient\Document\PatientDocZipGeneratorController@generatePatientDocZip');

            /** @see \App\Http\Controllers\Api\Patient\Document\PatientDocZipGeneratorController::downloadPatientDocZip() */
            Route::get('{patient}/documents-zip/download/{fileName}', 'Patient\Document\PatientDocZipGeneratorController@downloadPatientDocZip'); 

            /** @see \App\Http\Controllers\Api\Patient\Document\PatientDocumentController::getBase64() */
            Route::get('{patient}/documents/{patient_document}', 'Patient\Document\PatientDocumentController@getBase64');

            /** @see \App\Http\Controllers\Api\Patient\Document\PatientDocumentController::sendViaEmail() */
            Route::post('{patient}/documents/send', 'Patient\Document\PatientDocumentController@sendViaEmail');

            Route::get('{patient}/patient-notes/draft', 'Patient\Note\PatientNoteController@getDraft');

            Route::post('patient-alert', 'Patient\PatientController@addPatientAlert'); 

            Route::get('{patient}/is-synchronized', 'Patient\PatientController@checkIsSynchronized');
        });
    });

    Route::prefix('safe')->group(function () {
        /** @see \App\Http\Controllers\Api\PatientForm\PatientFormController::storeAll() */
        Route::post('patients/{encrypted_patient}/forms/{document_request}', 'PatientForm\PatientFormController@storeAll');
        Route::post('patients/{encrypted_patient}/forms/{document_request}/credit-card', 'PatientForm\PatientFormController@storeCreditCard');
        Route::post('patients/{encrypted_patient}/forms/{document_request}/send-to-email', 'PatientForm\PatientDocumentRequestSharedDocumentController@store');
    });

    Route::prefix('providers')->middleware('admin')->group(function () {
        Route::post('availability', 'Availability\Admin\AvailabilityController@show');
        Route::get('availability/insurances', 'Availability\Admin\AvailabilityController@getProviderInsurances');
        Route::get('availability/insurances/providers', 'Availability\Admin\AvailabilityController@getInsuranceProviders');
        Route::post('{provider}/tridiuum-provider', 'Tridiuum\ProviderController@assign');
        Route::patch('{provider}', 'ProviderController@update');
    });

    Route::prefix('tridiuum-providers')->middleware('admin')->group(function () {
        Route::get('', 'Tridiuum\ProviderController@index');
    });

    Route::prefix('system')->middleware(['user-provider', 'profile-completed'])->group(function () {
        Route::get('diagnoses/autocomplete', 'DiagnoseController@autocomplete');
        Route::get('insurances', 'System\InsuranceController@index')->middleware('admin');
        Route::put('insurances/plans/{plan}', 'System\InsurancePlanController@update')->middleware('admin');
        Route::get('billing-periods/previous', 'System\BillingPeriodController@previous');
    });

    Route::prefix('system')->middleware(['admin'])->group(function () {
        Route::get('parsers', 'ParserController@index');
        Route::post('parsers/run', 'ParserController@run');
        Route::get('billing-periods', 'System\BillingPeriodController@index');
    });

    Route::post('/webhook/twilio/sms', 'Webhook\Twilio\SmsController@sms');

    Route::prefix('secretaries-dashboard')->middleware(['admin-secretary'])->group(function () {
        Route::get('important-for-today', 'SecretariesDashboardController@getImportantForToday');
        Route::get('new-lost-patients', 'SecretariesDashboardController@getNewLostPatients');
        Route::get('tridiuum-appointments-data', 'SecretariesDashboardController@getTridiuumAppointmentsData');
        Route::post('restart-tridiuum-parsers', 'SecretariesDashboardController@restartTridiuumParsers');
    });

    Route::prefix('new-patients-crm')->middleware(['admin-secretary'])->group(function () {
        Route::get('/stages', 'NewPatientsCrm\PatientInquiryStageController@index');
        Route::get('/sources', 'NewPatientsCrm\PatientInquirySourceController@index');
        Route::post('/sources', 'NewPatientsCrm\PatientInquirySourceController@createSource');
        Route::get('/channels', 'NewPatientsCrm\PatientInquiryChannelController@index');
        Route::get('/registration-methods', 'NewPatientsCrm\PatientInquiryRegistrationMethodController@index');

        Route::get('/leads/inquirables-without-active-inquiries', 'NewPatientsCrm\PatientLeadController@getInquirablesWithoutActiveInquiries');

        Route::put('/leads/comments/{comment}', 'NewPatientsCrm\PatientLeadController@update');
        Route::delete('/leads/comments/{comment}', 'NewPatientsCrm\PatientLeadCommentController@destroy');

        Route::post('/leads/{patientLead}/upload-file', 'NewPatientsCrm\PatientLeadController@uploadFile');
        Route::put('/leads/documents/{document}/set-type', 'NewPatientsCrm\PatientLeadController@setDocumentType');
        Route::delete('/leads/documents/{document}', 'NewPatientsCrm\PatientLeadController@deleteDocument');

        Route::prefix('inquiries')->group(function () {
            Route::get('/', 'NewPatientsCrm\PatientInquiryController@getInquiriesByStage');
            Route::get('/archived', 'NewPatientsCrm\PatientInquiryController@getArchiveInquiries');
            Route::post('/', 'NewPatientsCrm\PatientInquiryController@create');
            Route::put('/{inquiry}', 'NewPatientsCrm\PatientInquiryController@update');

            Route::post('/{inquiry}/change-stage', 'NewPatientsCrm\PatientInquiryController@changeStage');
            Route::post('/{inquiry}/create-patient-from-patient-lead', 'NewPatientsCrm\PatientInquiryController@createPatientFromPatientLead');
            Route::post('/{inquiry}/archive', 'NewPatientsCrm\PatientInquiryController@archive');
            Route::post('/{inquiry}/close', 'NewPatientsCrm\PatientInquiryController@close');

            Route::get('/{inquiry}/comments', 'NewPatientsCrm\PatientInquiryController@getComments');
            Route::post('/{inquiry}/comments', 'NewPatientsCrm\PatientInquiryController@createComment');
            Route::post('/{inquiry}/initial-survey-comment', 'NewPatientsCrm\PatientInquiryController@createInitialSurveyComment');
            Route::post('/{inquiry}/second-survey-comment', 'NewPatientsCrm\PatientInquiryController@createSecondSurveyComment');
            Route::post('/{inquiry}/onboarding-complete-comment', 'NewPatientsCrm\PatientInquiryController@createOnboardingCompleteComment');
            Route::get('/{inquiry}/completed-initial-appointment', 'NewPatientsCrm\PatientInquiryController@getCompletedInitialAppointment');
            Route::get('/{inquiry}/completed-appointments', 'NewPatientsCrm\PatientInquiryController@getCompletedAppointments');
        });
    });

    Route::prefix('supervisor')->middleware(['admin-or-supervisor'])->group(function () {
        Route::get('/', 'SupervisingController@getSupervisors');
        Route::get('/supervisees', 'SupervisingController@getSupervisees');
    });

    Route::prefix('reauthorization-request-dashboard')->middleware(['admin-secretary'])->group(function () {
        Route::prefix('upcoming-reauthorization-requests')->group(function () {
            Route::get('/', 'ReauthorizationRequestDashboard\ReauthorizationRequestDashboardController@getUpcomingReauthorizationRequests');
            Route::get('/expirations', 'ReauthorizationRequestDashboard\ReauthorizationRequestDashboardController@getExpirationsList');
        });

        Route::prefix('submitted-reauthorization-request-forms')->group(function () {
            Route::get('/', 'ReauthorizationRequestDashboard\ReauthorizationRequestDashboardController@getSubmittedReauthorizationRequestForms');
            Route::post('/', 'ReauthorizationRequestDashboard\ReauthorizationRequestDashboardController@createReauthorizationRequestForm');
            Route::get('/stages', 'ReauthorizationRequestDashboard\ReauthorizationRequestDashboardController@getStages');
            Route::put('/{form}/change-stage', 'ReauthorizationRequestDashboard\ReauthorizationRequestDashboardController@changeStage');
            Route::post('/{form}/create-log', 'ReauthorizationRequestDashboard\ReauthorizationRequestDashboardController@createLog');
            Route::post('/{form}/future-insurance-reauthorization-data', 'ReauthorizationRequestDashboard\ReauthorizationRequestDashboardController@saveFutureInsuranceReauthorizationData');
            Route::put('/auth-number', 'ReauthorizationRequestDashboard\ReauthorizationRequestDashboardController@updateAuthNumber');
        });
    });

    Route::prefix('doctors-requests-dashboard')->middleware(['admin-secretary'])->group(function () {
        Route::prefix('patient-removal-requests')->group(function () {
            Route::get('/', 'DoctorsRequestsDashboardController@getPatientRemovalRequestsList');
        });

        Route::prefix('patient-note-unlock-requests')->group(function () {
            Route::get('/', 'DoctorsRequestsDashboardController@getPatientNoteUnlockRequestsList');
        });
    });
});

Route::get('/gmail/link', 'Api\Tridiuum\GmailApiTokenController@store');

Route::middleware(['admin'])->group(function () {
    Route::get('/check-charge-for-cancellation', 'Api\Patient\CheckChargeForCancellationController@index');
    Route::put('/check-charge-for-cancellation/{patient}', 'Api\Patient\CheckChargeForCancellationController@update');
});

Route::middleware(['admin'])->group(function () {
    Route::post('/remove-email-from-reject-list/{patient}', 'Api\MandrillRejectedEmailController@removeEmailFromRejectList');
});

Route::post('/frontent-logs/capture-message', 'Api\FrontendLogController@captureMessage');

// @todo remove
// Route::get('test', function () {
//     $appointment = \App\Appointment::orderBy('created_at', 'desc')->limit(1)->first();

//     $appointment->patient()->first()->tags()->detach(PatientTag::getTransferringId());

//     return response()->json([
//         'res' => 'ok'
//     ]);
// });
