<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/**
 * If you're reading this one, then you've been transferred to my last project. I am so sorry. Good luck.
 */

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// --------------------------------
// use Illuminate\Support\Facades\File;
// use Illuminate\Support\Facades\Response;

// Route::get('storage/{filename}', function ($filename)
// {
//     $path = storage_path('app/photos/' . $filename);

//     if (!File::exists($path)) {
//         abort(404);
//     }

//     $file = File::get($path);
//     $type = File::mimeType($path);

//     $response = Response::make($file, 200);
//     $response->header("Content-Type", $type);

//     return $response;
// });
// ----------------------------------

// MANDRILL WEBHOOKS
Route::get('/mandrill-webhooks', 'Webhooks\MandrillWebhooksController@index');
Route::post('/mandrill-webhooks', 'Webhooks\MandrillWebhooksController@index');

// Ringcentral webhooks
Route::post('/ringcentral-webhook', 'Webhooks\RingcentralWebhookController@index');

Route::get('/', function () {
    return view('auth.login');
})->name('home')->middleware('guest');

Route::get('/signature/{token}', 'Dashboard\UsersController@showUpdateSignatureForm')->name('signature.show-update-form');
Route::post('/signature/{token}', 'Dashboard\UsersController@saveSignatureWithToken');

//AUTH
Auth::routes();
Route::get('/register/complete', 'UsersController@registrationComplete')->name('registration-complete');
Route::get('login/google', 'Auth\LoginController@redirectToProvider')->name('login.google');
Route::get('login/google/callback', 'Auth\LoginController@handleProviderCallback')->name('login.google.callback');

Route::prefix('system')->middleware(['system'])->namespace('System')->group(function () {
    Route::get('', 'SystemController@index');
    Route::delete('queue-jobs', 'QueueJobController@bulkDelete')->name('system.queue-jobs.delete');
});

//dashboard

Route::prefix('dashboard')->middleware(['admin'])->group(function () {

    Route::post('complaint-reviewed', 'Dashboard\ProviderSalaryController@complaintReviewed')->name('dashboard-salary.complaint-reviewed');

    Route::get('tridiuum/invalid-credentials', 'Dashboard\TridiuumController@getProviderInvalidCredentials')->name('dashboard.users.create');

    Route::get('users/create', 'Dashboard\UsersController@create')->name('dashboard.users.create');
    Route::post('users/create', 'Dashboard\UsersController@store')->name('dashboard.users.store');
    Route::post('users/change-role', 'Dashboard\UsersController@changeRole')->name('dashboard.users.change-role');
    Route::get('users/{userId}/meta', 'Dashboard\UsersController@getSecretaryMeta')->name('dashboard.users.get-meta');
    Route::put('users/{userId}/meta', 'Dashboard\UsersController@updateSecretaryMeta')->name('dashboard.users.update-meta');

    Route::get('patients/for-square', 'PatientController@getPatientsForSquare');
    Route::get('patients/{patient}/square/customers', 'Dashboard\PatientSquareAccountController@getPatientCustomers');


    Route::prefix('square')->group(function () {
        Route::get('customers/unattached', 'Dashboard\PatientSquareAccountController@unattached')
            ->name('dashboard.square.customers.unattached');
        Route::put('customers/{customer}', 'Dashboard\PatientSquareAccountController@update')
            ->name('dashboard.square.customers.update');
        Route::delete('customers/{customer}', 'Dashboard\PatientSquareAccountController@detach')
            ->name('dashboard.square.customers.detach');
    });

    Route::prefix('patient-removal-requests')->group(function () {
        Route::get('', 'PatientRemovalRequestController@index')->name('patient-removal-requests.index');
        Route::put('accept', 'PatientRemovalRequestController@accept')
            ->name('patient-removal-requests.accept');
        Route::put('decline', 'PatientRemovalRequestController@decline')
            ->name('patient-removal-requests.decline');
    });

    Route::prefix('patient-note-unlock-requests')->group(function () {
        Route::get('', 'PatientNoteUnlockRequestController@index')
            ->name('patient-note-unlock-requests.index');
        Route::put('accept', 'PatientNoteUnlockRequestController@accept')
            ->name('patient-note-unlock-requests.accept');
        Route::put('decline', 'PatientNoteUnlockRequestController@decline')
            ->name('patient-note-unlock-requests.decline');
    });

    Route::post('adjustment', 'PatientTransactionAdjustmentController@store');

    Route::prefix('posting')->group(function () {
        Route::get('', 'PatientTransactionController@getOfficeallyPaymentsForPosting');
        Route::post('', 'PatientTransactionController@makePosting');
    });

    Route::prefix('appointments')->group(function () {
        Route::match(['get', 'post'], 'completed', 'AppointmentController@getCompletedAppointments');
        Route::get('inprogress-visits-count', 'AppointmentController@getInprogressVisitCount');
        Route::post('create-visit', 'AppointmentController@createVisit');

        // @todo remove
        Route::get('kaiser', 'AppointmentController@viewKaiserAppointments')->name('appointments.kaiser');
        Route::get('kaiser/get', 'AppointmentController@getKaiserAppointments')->name('appointments.kaiser.get');
        Route::get('kaiser/get/{id}', 'AppointmentController@getKaiserAppointmentsDetail');
        Route::put('kaiser/update/{id}', 'AppointmentController@updateKaiserAppointment');
        Route::post('kaiser/ringout', 'AppointmentController@ringout');
        Route::put('kaiser/call-logs/{id}', 'AppointmentController@callLogUpdate');
        Route::get('kaiser/sites', 'AppointmentController@getKaiserSites');
        // end todo

        Route::get('ehr', function () {
            return view('dashboard.appointments.ehr');
        })->name('appointments.ehr');
    });

    Route::prefix('system-messages')->group(function () {
        Route::get('', 'Dashboard\SystemMessageController@index')->name('system-messages');

        Route::get('add', 'Dashboard\SystemMessageController@showAdd')->name('system-messages.add');
        Route::post('add', 'Dashboard\SystemMessageController@add')->name('system-messages.add');

        Route::post('{id}/delete', 'Dashboard\SystemMessageController@delete')->name('system-messages.delete')->where(['id' => '\d+']);
        Route::get('{id}/edit', 'Dashboard\SystemMessageController@showEdit')->name('system-messages.edit')->where(['id' => '\d+']);
        Route::post('{id}/edit', 'Dashboard\SystemMessageController@edit')->name('system-messages.edit')->where(['id' => '\d+']);
    });

    Route::post('delete-patient-provider', 'Dashboard\PatientHasProviderController@deletePatientProviderRelationship');

    Route::post('add-patient-provider', 'Dashboard\PatientHasProviderController@addPatientProviderRelationship');

    Route::get('mandrill-statistic', 'DocumentDownloadController@getMandrillLog');

    Route::get('patient/{id}/available-providers', 'Dashboard\DoctorsController@getAvailableProvidersForPatient');

    Route::post('uncheck-paper-notes', 'AppointmentController@uncheckPaperNotes');

    Route::post('enable-or-disable-user', 'Dashboard\DoctorsController@enableOrDisable')->name('user.enableOrDisable');

    Route::post('allow-editing-document', 'Dashboard\DoctorsController@allowEditingDocument');

    Route::post('allow-editing-note', 'Dashboard\DoctorsController@allowEditingNote');
    Route::post('allow-editing-assessment-form', 'Dashboard\DoctorsController@allowEditingAssessmentForm');
    Route::post('delete-note', 'Dashboard\DoctorsController@deleteNote');

    Route::get('doctors', 'Dashboard\DoctorsController@index')->name('dashboard-doctors');
    Route::get('doctors/api', 'Dashboard\DoctorsController@indexApi')->name('dashboard-doctors-api');
    Route::post('invite', 'Dashboard\DoctorsController@invite')->name('invite');
    Route::get('doctors-availability', 'Dashboard\DoctorsController@availability')->name('dashboard-doctors-availability');
    Route::post('doctors', 'Dashboard\DoctorsController@saveDoctorProviderRelation');
    Route::post('doctors/tariff-plan', 'Dashboard\DoctorsController@saveProviderTariffPlanRelation');
    Route::post('doctors/billing-period', 'Dashboard\DoctorsController@saveProviderBillingPeriodRelation');
    Route::post('doctors/work-hours-per-week', 'Dashboard\DoctorsController@saveProviderWorkHoursPerWeek');
    Route::post('doctors/license-date', 'Dashboard\DoctorsController@saveProviderLicenseDate');
    Route::post('doctors/license-end-date', 'Dashboard\DoctorsController@saveProviderLicenseEndDate');
    Route::post('doctors/has-benefits', 'Dashboard\DoctorsController@saveProviderHasBenefits');
    Route::post('doctors/is-new', 'Dashboard\DoctorsController@saveProviderIsNew');
    Route::post('doctors/is-collect-payment-available', 'Dashboard\DoctorsController@saveProviderCollectPaymentAvailable');
    Route::post('doctors/is-associate', 'Dashboard\DoctorsController@saveProviderIsAssociate');
    Route::post('doctors/works-with-upheal', 'Dashboard\DoctorsController@saveProviderWorksWithUpheal');
    Route::post('users/delete', 'Dashboard\UsersController@delete');
    Route::post('parser/run', function () {
        exec('php ../artisan parser:run > /dev/null &');
        return response()->json([]);
    });

    Route::get('providers', 'Dashboard\TherapistsController@index')->name('dashboard-theraptists');
    Route::get('providers/api', 'Dashboard\TherapistsController@indexApi');
    Route::get('providers/filter-options', 'Dashboard\TherapistsController@getFiltersOptions');

    Route::post('document/delete', 'Dashboard\UsersController@deleteDocument');

    Route::get('patients/statistic-for-diagrams', 'Dashboard\UsersController@getStatisticForDiagram');
    Route::post('patients/assigned-to-therapists-statistic-for-diagrams', 'Dashboard\UsersController@getPatientAssignedToTherapistsStatisticForDiagrams');
    Route::post('patients/wo-appointments/statistic', 'Dashboard\UsersController@getPatientsWithoutAppointmentsStatistic');
    Route::post('patients/statistic/stop-watching', 'Dashboard\UsersController@stopWatching');
    Route::post('patients-without-appointments/statistic/stop-watching', 'Dashboard\UsersController@stopWatchingForPatientsWoAppointments');

    Route::get('/statistic/sent-documents', 'PatientDocumentController@getSentDocumentsStatistic');
    Route::get('/statistic/sent-documents-by-email', 'PatientDocumentController@getSentDocumentsByEmailStatistic');
    Route::get('/statistic/sent-documents-by-fax', 'PatientDocumentController@getSentDocumentsByFaxStatistic');

    Route::get('/statistic/therapists-availability', 'Dashboard\ProviderAvailabilityController@providersWithTotalAvailability');
    Route::get('/statistic/therapists-availability/{provider}/history', 'Dashboard\ProviderAvailabilityController@providerAvailabilityHistory');

    Route::get('/get-documents-to-send/reauthorization-requests', 'PatientDocumentController@getDocumentsToSendReauthorizationRequests');
    Route::get('/get-documents-to-send/discharge-summary', 'PatientDocumentController@getDocumentsToSendDischargeSummary');
    Route::get('/get-documents-to-send/initial-assessment', 'PatientDocumentController@getDocumentsToSendInitialAssessment');
    Route::post('/mark-document-as-sent', 'PatientDocumentController@markDocumentAsSent');
    Route::post('/approve-sent-document', 'PatientDocumentController@approveSentDocument');

    Route::post('providers/statistic/total-vc-and-pn', 'Dashboard\DoctorsController@getTotalVcAndPnStatistic');

    Route::get('check-visits-parser', 'Dashboard\ProviderSalaryController@checkVisitsParser');

    Route::prefix('salary')->group(function () {
        Route::post('/sync-visits', 'Dashboard\ProviderSalaryController@syncVisits')->name('sync-visits');
        Route::match(['get', 'post'], '/', 'Dashboard\ProviderSalaryController@index')->name('dashboard-salary');
        Route::get('/providers-for-salary-quota', 'Dashboard\ProviderSalaryController@getProvidersForSalaryQuota');
        Route::post('/calculate-salary', 'Dashboard\ProviderSalaryController@calculateSalary');
        Route::get('{provider}/details', 'Dashboard\ProviderSalaryController@details');

        Route::post('/{id}/download', 'Dashboard\ProviderSalaryController@download')->name('dashboard-salary-download');
    });

    Route::resource('tariffs-plans', 'Dashboard\TariffsPlansController');
    Route::prefix('tariffs-plans')->group(function () {
        Route::post('/prices', 'Dashboard\TariffsPlansController@storePrices')->name('dashboard-tariffs-plans-prices');
        Route::post('/group-insurance-plans', 'Dashboard\TariffsPlansController@groupInsurancePlans')->name('dashboard-group-insurance-plans');
        Route::post('/ungroup-insurance-plans', 'Dashboard\TariffsPlansController@ungroupInsurancePlans')->name('dashboard-ungroup-insurance-plans');
    });

    Route::prefix('timesheets')->middleware(['user-provider']);

    Route::get('/reauthorization-request', function () {
        return \File::get(public_path() . '/react-app/index.html');
    })->name('react.layouts')->middleware(['admin-secretary']);

    Route::get('/doctors-requests', function () {
        return \File::get(public_path() . '/react-app/index.html');
    })->name('react.layouts')->middleware(['admin-secretary']);
    
    Route::get('/patients-management', function () {
        return \File::get(public_path() . '/react-app/index.html');
    })->name('react.layouts')->middleware(['admin-or-supervisor']);

    //PUT YOUR DASHBOARD ROUTES BEFORE THIS COMMENT
    Route::match(['get', 'post'], '{vue_route}', function () {
        return view('layouts.app');
    })->where('vue_route', '.*');   //dashboard secured routes
});

Route::prefix('dashboard')->middleware(['user-provider'])->group(function () {
    Route::put('patients/{patient}', 'PatientController@update');
    Route::put('patients/language/{patient}', 'Api\Patient\PatientController@updatePatientLanguagePrefer');
    Route::put('patients/secondary-email/{patient}', 'Api\Patient\PatientController@addSecondaryEmail');
    Route::put('patients/visit-frequency/{patient}', 'Api\Patient\PatientController@updatePatientVisitFrequency');
    // Route::post('patients/patient-alert', 'Api\Patient\PatientController@addPatientAlert'); 
});

Route::prefix('update-notifications')->middleware('auth')->group(function () {
    Route::get('/', 'UpdateNotificationController@index')->middleware('only-admin')->name('update-notifications.index');
    Route::get('/api', 'UpdateNotificationController@indexApi')->middleware('only-admin');
    Route::get('/create', 'UpdateNotificationController@create')->middleware('only-admin');
    Route::post('/', 'UpdateNotificationController@store')->middleware('only-admin')->name('update-notifications.store');
    Route::get('/history', 'UpdateNotificationController@history')->name('update-notifications.history');
    Route::get('/history/api', 'UpdateNotificationController@historyApi');
    Route::get('/available-list', 'UpdateNotificationController@availableList');
    Route::get('/{notification}', 'UpdateNotificationController@show')->middleware('only-admin');
    Route::get('/{notification}/edit', 'UpdateNotificationController@edit')->middleware('only-admin');
    Route::put('/{notification}', 'UpdateNotificationController@update')->middleware('only-admin')->name('update-notifications.update');
    Route::delete('/{notification}', 'UpdateNotificationController@destroy')->middleware('only-admin');
    Route::post('/{notification}/mark-as-opened', 'UpdateNotificationController@markAsOpened');
    Route::post('/{notification}/mark-as-viewed', 'UpdateNotificationController@markAsViewed');
    Route::get('/{notification}/viewed-list', 'UpdateNotificationController@viewedList')->middleware('only-admin');
    Route::get('/{notification}/viewed-list/api', 'UpdateNotificationController@viewedListApi')->middleware('only-admin');
    Route::post('/{notification}/remind-later', 'UpdateNotificationController@remindLater');
});

Route::middleware('admin-secretary')->group(function () {
    Route::get('users/{user}/update-notifications', 'UpdateNotificationController@userNotifications');
    Route::get('users/{user}/update-notifications/api', 'UpdateNotificationController@userNotificationsApi');
});

Route::prefix('update-notification-templates')->middleware('only-admin')->group(function () {
    Route::get('/', 'UpdateNotificationTemplateController@index')->name('update-notification-templates.index');
    Route::get('/api', 'UpdateNotificationTemplateController@indexApi');
    Route::get('/create', 'UpdateNotificationTemplateController@create');
    Route::post('/', 'UpdateNotificationTemplateController@store')->name('update-notification-templates.store');
    Route::get('/{template}', 'UpdateNotificationTemplateController@show');
    Route::get('/{template}/edit', 'UpdateNotificationTemplateController@edit');
    Route::put('/{template}', 'UpdateNotificationTemplateController@update')->name('update-notification-templates.update');
    Route::delete('/{template}', 'UpdateNotificationTemplateController@destroy');
});

Route::prefix('update-notification-substitutions')->middleware('only-admin')->group(function () {
    Route::get('/api', 'UpdateNotificationSubstitutionController@indexApi');
});

//CHART
Route::prefix('provider')->middleware(['profile-completed'])->group(function () {

    Route::get('preview-file/{fileName}', 'Api\Provider\ProviderCommentController@previewFile')->middleware(['admin-secretary']);

    Route::get('/download-file/{fileName}', 'Api\Provider\ProviderCommentController@downloadFile')->middleware(['admin-secretary']);

    Route::get('has-patient/{id}', 'ProviderController@hasPatient');

    Route::get('missing-notes-count', 'ProviderController@getMissingNotesCount');

    Route::get('messages', 'ProviderController@getMessages');

    Route::post('messages/set-read', 'ProviderController@setReadMessage');

    Route::get('dataset-for-tribute', 'ProviderController@getProvidersDatasetForTribute');

    Route::get('', 'ProviderController@getProvider');

    Route::get('all', 'ProviderController@getProviderList');

    Route::get('appointments', 'ProviderController@getAppointments');

    Route::get('past-appointments', 'AppointmentController@getPastAppointments');

    Route::get('patients-search', 'ProviderController@patientsSearch');

    Route::get('{id}/patients', 'ProviderController@getPatients');

    Route::get('patients', 'ProviderController@providerPatients');

    Route::get('today-patients', 'ProviderController@providerTodayPatients');

    Route::post('get-signature', 'ProviderController@getProviderSignature');

    Route::prefix('availability-calendar')->group(function () {
        Route::get('appointments', 'ProviderAvailabilityCalendarController@getAppointments');
        Route::get('work-hours', 'ProviderAvailabilityCalendarController@getWorkHours');
        Route::post('work-hours', 'ProviderAvailabilityCalendarController@addWorkHours');
        Route::put('work-hours', 'ProviderAvailabilityCalendarController@updateWorkHours');
        Route::post('work-hours/delete', 'ProviderAvailabilityCalendarController@deleteWorkHours');
        Route::post('work-hours/copy', 'ProviderAvailabilityCalendarController@copyLast');
        Route::get('work-hours/check-copy', 'ProviderAvailabilityCalendarController@checkCopy');
        Route::get('work-hours/check-week-confirmation', 'ProviderAvailabilityCalendarController@checkWeekConfirmation');
        Route::get('work-hours/total', 'ProviderAvailabilityCalendarController@getTotalWorkHours');
        Route::post('work-hours/confirm-week', 'ProviderAvailabilityCalendarController@confirmWeek');

        Route::get('weeks', 'ProviderAvailabilityCalendarController@getWeeksStatus');
        Route::get('check-completed', 'ProviderAvailabilityCalendarController@checkWeeksCompleted');
        Route::post('weeks', 'ProviderAvailabilityCalendarController@makeWeekCompleted');
        Route::get('get-event-max-time', 'ProviderAvailabilityCalendarController@getEventMaxTime');
        Route::group(['middleware' => ['user-provider']], function () {
            Route::get('get-notifications', 'AppointmentController@getNotifications')->name('appointments.get_notifications');
            Route::post('confirm-notifications', 'AppointmentController@confirmNotifications')->name('appointments.confirm_notifications');
        });
    });

    Route::get('score', 'ProviderAvailabilityCalendarController@getScopeByWeeks');

    Route::middleware('user-provider')->group(function () {
        Route::get('missing-notes', 'ChartDashboardController@getProviderMissingNotes');
        Route::get('missing-initial-assessments', 'ChartDashboardController@getProviderMissingInitialAssessments');
        Route::get('missing-copay', 'ChartDashboardController@getProviderMissingCopay');
        Route::get('reauthorization-requests', 'ChartDashboardController@getReauthorizationRequests');
        Route::get('assigned-patients', 'ChartDashboardController@getAssignedPatients');
        Route::get('inactive-patients', 'ChartDashboardController@getInactivePatients');
        Route::get('visits-dataset-for-chart', 'ChartDashboardController@getVisitsDatasetForChart');
    });

    Route::prefix('patient-forms')->namespace('PatientForm')->middleware(['user-provider'])->group(function () {
        Route::get('/index', 'PatientFormController@index');
    });

    Route::get('/{patient}/{treatmentModality}/fee-per-visit', 'ProviderController@getFeePerVisit'); 
});

Route::middleware('user-provider')->group(function () {
    Route::get('/user/is-admin', 'UsersController@isAdmin');
    Route::get('/user/is-only-admin', 'UsersController@isOnlyAdmin');
    Route::get('/user/is-audit', 'UsersController@isAudit');
    Route::get('/user/is-secretary', 'UsersController@isSecretary');
    Route::get('/user/is-patient-relation-manager', 'UsersController@isPatientRelationManager');
    Route::get('/user/is-supervisor', 'UsersController@isSupervisorOrAdmin');

    Route::get('/user/role', 'UsersController@getUserRoles');

    Route::get('/user/meta', 'UsersController@getUserMeta');

    Route::get("/user/training", "TrainingController@index")
        ->name("training")
        ->middleware([
            'redirect-if-has-unresolved-notifications',
            'redirect-if-has-unresolved-appointments',
            'redirect-if-timesheet-is-not-completed',
            'redirect-if-has-reauthorization-requests',
        ]);
    Route::post("/user/training/certificate", "TrainingController@downloadCertificate")
        ->name("training.certificate");

    Route::post('/user/harassment-certificate', 'TrainingController@uploadHarassmentCertificate');
    Route::get('/user/harassment-certificate', 'TrainingController@getHarassmentCertificateName');    

    Route::get("/user/exams", "ExamsController@index")->name("exams");
    Route::post("/user/exams", "ExamsController@showResult")->name("exams.showResult");

    Route::get('offices', 'OfficeController@getAllOffices');
    Route::get('offices-rooms', 'OfficeRoomController@index');
    Route::get('offices-rooms/free/', 'OfficeRoomController@getFreeRooms')->name('free-office-rooms');

    Route::prefix('system-messages')->group(function () {
        Route::get('get', 'Dashboard\SystemMessageController@get');
        Route::post('set-readed', 'Dashboard\SystemMessageController@setReaded');
    });

    Route::get('user/is-password-outdated', 'UsersController@isPasswordOutdated');
    Route::get('document-default-faxes', 'PatientDocumentTypesController@getDocumentDefaultFaxes');
    Route::get('document-default-emails', 'PatientDocumentTypesController@getDocumentDefaultEmails');

    Route::get('profile/{id?}', 'Dashboard\DoctorsController@profileShow')
        ->name('profile.index')
        ->middleware([
            'redirect-if-has-unresolved-notifications',
            'redirect-if-has-unresolved-appointments',
            'redirect-if-timesheet-is-not-completed',
            'redirect-if-has-reauthorization-requests',
        ]);
    Route::get('profile/{id}/supervisees/api', 'Dashboard\DoctorsController@profileSuperviseesApi');

    Route::get('profile-status', 'Dashboard\DoctorsController@profileStatus')->name('profile.status');
    Route::post('profile', 'Dashboard\DoctorsController@profileStore')->name('profile.store');
    Route::post('profile-tridiuum', 'Dashboard\DoctorsController@profileTridiuumStore')->name('profile.store_tridiuum');
    Route::delete('profile-tridiuum', 'Dashboard\DoctorsController@profileTridiuumDelete')->name('profile.delete_tridiuum');
    Route::get('doctors/signature/{id?}', 'Dashboard\DoctorsController@showSignatureForm')
        ->where(['id' => '\d+'])
        ->name('show-signature-form');
    Route::post('users/save-signature', 'Dashboard\UsersController@saveSignature');
    Route::post('users/send-sms-to-update-signature', 'Dashboard\UsersController@sendSmsToUpdateSignature');
    Route::get('change-password', 'Dashboard\PasswordController@form')->name('change-password.form');
    Route::post('change-password', 'Dashboard\PasswordController@store')->name('change-password.store');

    Route::middleware('profile-completed')->group(function () {
        Route::post('providers/statistic', 'Dashboard\DoctorsController@getStatistic');
        Route::post('patients/statistic', 'Dashboard\UsersController@getStatistic');
        Route::post('patients/assigned-to-therapists/statistic', 'Dashboard\UsersController@getPatientsAssignedToTherapistsStatistic');
        Route::post('patients/upcoming-reauthorization-requests', 'Dashboard\UsersController@getUpcomingReauthorizationRequests');
    });

    Route::post('patient/sync-with-office-ally', 'PatientController@syncPatientWithOfficeAlly');
    Route::get('appointment/cancel-statuses', 'AppointmentController@getCancelStatuses');

    Route::get('patient/{patientId}/has-initial-assessment', 'PatientController@hasInitialAssessmentForm');

    Route::get('reassign-provider', 'UsersController@showReassignProvider')->name('reassign-provider');
    Route::post('reassign-provider', 'UsersController@reassignProvider');

    Route::get('treatment-modalities', 'TreatmentModalityController@index'); 

    Route::post('/empty-request', 'UsersController@emptyRequest');
});


Route::get('/document-download-page/{documentName}', 'PatientDocumentController@index')->name('document-download.index');
Route::post('/document-download-page', 'PatientDocumentController@downloadDocument')->name('document-download.download');
Route::get('/document-download-success', 'PatientDocumentController@downloadSuccess')->name('document-download.success');


Route::prefix('patient')->middleware(['user-provider', 'profile-completed'])->group(function () {

    Route::post('removal-request', 'PatientRemovalRequestController@send');
    Route::delete('removal-request', 'PatientRemovalRequestController@cancel');
    Route::get('{patientId}/removal-requests/active', 'PatientRemovalRequestController@activeRequests')
        ->where(['patientId' => '\d+']);

    Route::prefix('notes')->group(function () {
        Route::post('unlock-request', 'PatientNoteUnlockRequestController@send');
        Route::delete('unlock-request', 'PatientNoteUnlockRequestController@cancel');
        Route::get('{patientNoteId}/unlock-requests/active', 'PatientNoteUnlockRequestController@activeRequests')
            ->where(['patientNoteId' => '\d+']);
    });

    Route::get('{patientId}/preprocessed-transactions', 'PatientTransactionController@getPreprocessed')->where(['patientId' => '\d+']);

    /** @see App\Http\Controllers\PatientTransactionController::getPreprocessedCount() */
    Route::get('{patient}/preprocessed-transactions/count', 'PatientTransactionController@getPreprocessedCount'); 

    Route::get('{id}/diagnoses', 'PatientController@getDiagnosesDataset')->where(['id' => '\d+']);

    Route::get('{id}/diagnoses-codes', 'PatientController@getDiagnosesCodesDataset')->where(['id' => '\d+']);

    Route::get('{id}/appointment-dates', 'PatientController@getAppointmentDates')->where(['id' => '\d+'])->name('appointment-dates');
    Route::get('{id}/appointment-document-dates', 'PatientController@getAppointmentDocumentDates')->where(['id' => '\d+']);

    Route::get('{id}/get-previous-note-data', 'PatientController@getPreviousNoteData')->name('pn.previous-data');

    Route::get('{patientId}/complete-appointment-data/{appointmentId}', 'PatientController@getCompleteAppointmentData')->name('complete-appointment-data');

    Route::get('{id}/appointments', 'PatientController@getPatientAppointments')->where(['id' => '\d+']);
    Route::get('{id}/visit-created-appointments', 'PatientController@getPatientVisitCreatedAppointments')->where(['id' => '\d+']);

    Route::get('{id}/notifications', 'PatientController@getPatientNotifications')->where(['id' => '\d+']); 

    Route::post('/save-note', 'PatientController@saveNote')->middleware('clear-string');

    //    Electronic documents
    Route::delete('/electronic-document/{document}', 'PatientElectronicDocumentController@destroy');
    Route::get('/electronic-document/{document}/download', 'PatientElectronicDocumentController@download');
    Route::post('/electronic-document', 'PatientElectronicDocumentController@store')->middleware('clear-string');
    Route::put('/electronic-document/{document}', 'PatientElectronicDocumentController@update')->middleware('clear-string');
    Route::get('/electronic-document/{document}', 'PatientElectronicDocumentController@show')->middleware('clear-string');

    Route::post('/quick-save-note', 'PatientController@quickSaveNote')->middleware('clear-string');

    Route::post('/update-note', 'PatientController@updateNote')->middleware('clear-string');

    Route::get('{patient}', 'PatientController@show')->where(['patient' => '\d+']);

    Route::get('{id}/patient-notes', 'PatientController@getPatientNotes'); 

    Route::get('{id}/patient-notes-with-documents', 'PatientController@getPatientNotesWithDocuments');

    Route::get('{id}/patient-notes-with-documents/count', 'PatientController@getPatientNotesWithDocumentsCount');

    Route::get('{id}/patient-documents', 'PatientController@getPatientDocuments');

    Route::get('{patient}/patient-note-and-appointment-count', 'PatientController@getPatientNoteAndAppointmentCount');   

    Route::get('/export-note/{id}', 'PatientController@exportNote');

    Route::get('/download-document/{documentName}', 'PatientController@downloadDocument')->name('patient.download-document');

    Route::get('/preview-document/{documentName}', 'PatientController@previewDocument')->middleware('auth');

    Route::post('/document-mail-send', 'PatientDocumentController@sendMail');

    Route::post('/document-fax-send', 'PatientDocumentController@sendFax')->name('patient.document-fax-send');

    Route::post('upload-file', "PatientController@uploadFile");

    Route::get('note-editing-status/{id}', "PatientController@isNoteEditingAllowed")
        ->where(['id' => '\d+']);

    Route::get('notes/{id}', 'PatientController@getNote')->where(['id' => '\d+']);

    Route::get('unfinalized-note/{id}', 'PatientController@getUnfinalizedNote')->where(['id' => '\d+']);

    Route::get('comment/{comment}', 'PatientCommentController@show');

    Route::get('{patientID}/comments', 'PatientController@getComments')->where(['patientID' => '\d+']);

    Route::post('comment', 'PatientCommentController@store');

    Route::delete('comment/{comment}', 'PatientCommentController@destroy')->middleware('admin');

    Route::post('document-comment/store', 'PatientDocumentController@storeDocumentComment');

    Route::post('check-exists-by-officeally-id', 'PatientController@ifExistsByOfficeAllyID');

    Route::get('first-appointment-date-hasnt-note', 'PatientController@getFirstAppointmentDateHasntNote');

    Route::get('statuses', 'PatientController@getStatusesList');

    Route::get('visit-frequencies', 'PatientController@getVisitFrequenciesList');

    Route::post('pn-unfinalized/delete', 'PatientController@deleteUnfinalizedNotes');

    Route::post('{id}/add-credit-card', 'PatientController@addCreditCard');

    Route::get('{patient}/check-late-cancellation-payment', 'CheckPatientLateCancellationPaymentController@index');
});

Route::prefix('appointment')->group(function () {
    Route::middleware(['user-provider', 'profile-completed'])->group(function () {
        Route::post('pay-co-pay', 'AppointmentController@payCoPay');
        Route::post('complete', 'AppointmentController@completeAppointment');
        Route::post('reschedule/{appointment}', 'AppointmentController@rescheduleAppointment');
        Route::post('cancel', 'AppointmentController@cancelAppointment');
        Route::get('other-cancel-statuses', 'AppointmentController@getOtherCancelStatuses');
        Route::get('reschedule-statuses', 'AppointmentController@getRescheduleStatuses');
        Route::get('reschedule-sub-statuses', 'AppointmentController@getRescheduleSubStatuses');
        Route::post('get-time-by-date', 'AppointmentController@getTimeByDate');
        Route::get('{appointment}/{treatmentModality}/fee-per-visit', 'AppointmentController@getFeePerVisit'); 
    });

    Route::middleware('admin')->group(function () {
        Route::post('change-on-paper-note', 'AppointmentController@changeOnPaperNote');
    });
});

Route::middleware(['user-provider', 'profile-completed'])->prefix('documents')->group(function () {
    Route::get('types-tree', 'PatientDocumentTypesController@getTree');

    Route::post('set-type', 'PatientController@setDocumentType');

    Route::post('change-status', 'PatientDocumentController@changeStatus')->middleware('admin');

    Route::post('delete', 'PatientController@deleteDocument');

    Route::post('get-type-id', 'PatientDocumentTypesController@getTypeID');

    Route::post('thumbnail', 'PatientController@getDocumentsThumbnail')->name('documents.thumbnail');
});

Route::prefix('forms')->group(function () {

    Route::post('/patient/upload-photo', 'PatientFormController@uploadPhoto');

    Route::post('/patient/check-exists-by-officeally-id', 'PatientController@ifExistsByOfficeAllyID');

    Route::post('/patient/check-exists-by-data', 'PatientController@ifExistsByData');

    Route::post('/is-doctor-password-valid', 'ProviderController@isDoctorPasswordValid');

    Route::get('', 'PatientFormController@index');

    Route::post('/patient/{id}', 'PatientFormController@getPatient');
    Route::post('/patient/{id}/add-credit-card', 'PatientController@addCreditCard');

    Route::post('/save-first-form', 'PatientFormFirstController@saveForm');

    Route::post('/save-second-form', 'PatientFormSecondController@saveForm');

    Route::get('/export-first/{id}', 'PatientFormFirstController@exportPdf');

    Route::match(['get', 'post'], '{vue_route}', function () {
        return view('forms.index');
    })->where('vue_route', '.*');
});

// patient-forms
Route::prefix('patient-forms')->group(function () {
    Route::post('/patient/{encrypted_patient}', 'Api\PatientForm\PatientController@show');
});

Route::prefix('f')->group(function () {
    Route::match(['get', 'post'], '{vue_route}', function () {
        return view('forms.index');
    })->where('vue_route', '.*');
});

Route::prefix('ff')->group(function () {
    Route::match(['get', 'post'], '{vue_route}', function () {
        return view('forms.index');
    })->where('vue_route', '.*');
});

Route::get('/chart', function () {
    return view('layouts.app');
})->name('vue-chart')->middleware([
    'user-provider',
    'profile-completed',
    'redirect-if-has-unresolved-notifications',
    'redirect-if-has-unresolved-appointments',
    'redirect-if-timesheet-is-not-completed',
    'redirect-if-has-reauthorization-requests',
]);

Route::get('/find-patient-by-phone','FindPatientByPhoneController@findPatientByPhone')->middleware([
    'user-provider',
    'profile-completed',
]);

Route::get('/faxes', function () {
    return \File::get(public_path() . '/react-app/index.html');
})->name('react.layouts')->middleware(['admin-secretary']);

Route::get('/secretary-dashboard', function () {
    return \File::get(public_path() . '/react-app/index.html');
})->name('react.layouts')->middleware(['admin-secretary']);

Route::get('/new-patients-dashboard', function () {
    return \File::get(public_path() . '/react-app/index.html');
})->name('react.layouts')->middleware(['admin-secretary']);

Route::get('/supervisor-dashboard', function () {
    return \File::get(public_path() . '/react-app/index.html');
})->name('react.layouts')->middleware(['admin-or-supervisor']);

// AssessmentFormsController
Route::prefix('assessment-forms')->middleware(['user-provider', 'profile-completed'])->group(function () {
    Route::get('/templates', 'AssessmentFormsController@indexTemplates');
    Route::post('/create', 'AssessmentFormsController@storeToNextcloud');
    Route::post('/{id}/save', 'AssessmentFormsController@save');
    Route::get('/{id}', 'AssessmentFormsController@get');
});

Route::get('past-appointments', function () {
    return view('layouts.app');
})->middleware(['user-provider', 'profile-completed'])->name('past-appointments');
Route::get('salary/time-records', function () {
    return view('layouts.app');

})->middleware(['user-provider', 'profile-completed'])->name('provider-timesheet');

Route::get('statistic/upcoming-reauthorization-requests', function () {
    return view('layouts.app');
})->middleware(['user-provider', 'profile-completed'])->name('reauthorization-requests');

//проблема если использовать ->where('vue_route', '.*'); - работать swagger не будет
Route::match(['get', 'post'], '{vue_route}', function () {
    return view('layouts.app');
})
    ->where('vue_route', '.*')
    ->middleware([
        'user-provider',
        'profile-completed',
        'redirect-if-has-unresolved-notifications',
        'redirect-if-has-unresolved-appointments',
        'redirect-if-timesheet-is-not-completed',
        'redirect-if-has-reauthorization-requests',
    ]);
