<?php

namespace App\Providers;

use App\Appointment;
use App\Availability;
use App\KaiserAppointment;
use App\Models\GoogleMeeting;
use App\Models\Patient\DocumentRequest\PatientDocumentRequest;
use App\Models\GoogleMeetingCallLog;
use App\Models\Patient\Lead\PatientLead;
use App\Models\Patient\PatientDiagnose;
use App\Models\Patient\PatientElectronicDocument;
use App\Models\Patient\PatientPreprocessedTransaction;
use App\Models\Patient\PatientNoteUnlockRequest;
use App\Models\Patient\PatientRemovalRequest;
use App\Models\Patient\PatientTransaction;
use App\Models\Patient\Visit\PatientVisitDiagnose;
use App\Models\Patient\PatientTransactionAdjustment;
use App\Models\Provider\SalaryTimesheetLateCancellation;
use App\Observers\AppointmentObserver;
use App\Observers\AvailabilityObserver;
use App\Observers\GoogleMeetingObserver;
use App\Observers\KaiserAppointmentObserver;
use App\Observers\PatientDiagnoseObserver;
use App\Observers\PatientDocumentRequestObserver;
use App\Observers\GoogleMeetingCallLogObserver;
use App\Observers\PatientAssessmentFormObserver;
use App\Observers\PatientCommentObserver;
use App\Observers\PatientDocumentObserver;
use App\Observers\PatientElectronicDocumentObserver;
use App\Observers\PatientLeadObserver;
use App\Observers\PatientObserver;
use App\Observers\PatientPreprocessedTransactionObserver;
use App\Observers\PatientRemovalRequestObserver;
use App\Observers\PatientTransactionObserver;
use App\Observers\PatientVisitDiagnoseObserver;
use App\Observers\PatientTransactionAdjustmentObserver;
use App\Observers\SalaryTimesheetLateCancellationObserver;
use App\Observers\UserObserver;
use App\Observers\PatientNoteObserver;
use App\Observers\PatientVisitObserver;
use App\Observers\ProviderObserver;
use App\Observers\GoogleDriveObservers\FaxObserver;
use App\Observers\GoogleDriveObservers\PatientFormObserver;
use App\Observers\GoogleDriveObservers\UserMetaObserver;
use App\Patient;
use App\PatientAssessmentForm;
use App\PatientComment;
use App\PatientDocument;
use App\PatientVisit;
use App\Provider;
use App\User;
use App\PatientNote;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Jobs\Tridiuum\Auth as TridiuumAuth;
use App\Models\FaxModel\Fax;
use App\Models\Patient\PatientForm;
use App\Models\PatientHasProvider;
use App\Observers\PatientHasProviderObserver;
use App\Models\Patient\DocumentRequest\PatientDocumentRequestItem;
use App\Observers\PatientDocumentRequestItemObserver;
use App\Observers\PatientDocumentSharedObserver;
use App\PatientDocumentShared;
use App\Models\Patient\DocumentRequest\PatientDocumentRequestSharedDocument;
use App\Observers\PatientDocumentRequestSharedDocumentObserver;
use App\Observers\PatientNoteDiagnosesObserver;
use App\PatientNoteDiagnoses;
use App\Observers\PatientDocumentCommentObserver;
use App\PatientDocumentComment;
use App\Models\Patient\PatientTemplate;
use App\Observers\PatientTemplateObserver;
use App\Models\Square\SquareTransaction;
use App\Observers\SquareTransactionObserver;
use App\Observers\PatientSquareAccountCardObserver;
use App\PatientSquareAccountCard;
use App\Observers\PatientSquareAccountObserver;
use App\PatientSquareAccount;
use App\Repositories\MenuApiForReactLayouts\MenuApiRepositoryInterface;
use App\UserMeta;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Support\Facades\Event;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->setMysqlTimezone();
        $amountOfNewUsers = User::getNewUsersCount();
        View::share('amount_of_new_users', $amountOfNewUsers);
        
        $patientRemovalRequestCount = 0;
        if (Schema::hasTable('patient_removal_requests')) {
            $patientRemovalRequestCount = PatientRemovalRequest::new()->count();
        }
        View::share('amount_of_new_patient_removal_requests', $patientRemovalRequestCount);
        $patientNoteUnlockRequestCount = 0;
        if (Schema::hasTable('patient_note_unlock_requests')) {
            $patientNoteUnlockRequestCount = PatientNoteUnlockRequest::new()->count();
        }
        View::share('amount_of_new_patient_note_unlock_requests', $patientNoteUnlockRequestCount);

        \Validator::extend('current_password_match', function($attribute, $value, $parameters, $validator) {
            return \Hash::check($value, \Auth::user()->password);
        });

        Validator::extend('check_tridiuum_auth', function($attribute, $value, $parameters, $validator) {
            return \Bus::dispatchNow(new TridiuumAuth($parameters[0], $parameters[1], true));
        });

        PatientElectronicDocument::observe(PatientElectronicDocumentObserver::class);
        PatientDocument::observe(PatientDocumentObserver::class);
        PatientAssessmentForm::observe(PatientAssessmentFormObserver::class);
        User::observe(UserObserver::class);
        Appointment::observe(AppointmentObserver::class);
        Provider::observe(ProviderObserver::class);
        PatientVisit::observe(PatientVisitObserver::class);
        PatientNote::observe(PatientNoteObserver::class);
        PatientDocumentRequest::observe(PatientDocumentRequestObserver::class);
        PatientComment::observe(PatientCommentObserver::class);
        GoogleMeetingCallLog::observe(GoogleMeetingCallLogObserver::class);
        Availability::observe(AvailabilityObserver::class);
        Patient::observe(PatientObserver::class);
        PatientLead::observe(PatientLeadObserver::class);
        GoogleMeeting::observe(GoogleMeetingObserver::class);
        PatientRemovalRequest::observe(PatientRemovalRequestObserver::class);
        KaiserAppointment::observe(KaiserAppointmentObserver::class);
        SalaryTimesheetLateCancellation::observe(SalaryTimesheetLateCancellationObserver::class);
        PatientHasProvider::observe(PatientHasProviderObserver::class);
        PatientDocumentRequestItem::observe(PatientDocumentRequestItemObserver::class);
        PatientDocumentShared::observe(PatientDocumentSharedObserver::class);
        PatientDocumentRequestSharedDocument::observe(PatientDocumentRequestSharedDocumentObserver::class);
        PatientNoteDiagnoses::observe(PatientNoteDiagnosesObserver::class);
        PatientDocumentComment::observe(PatientDocumentCommentObserver::class);
        PatientTemplate::observe(PatientTemplateObserver::class);
        PatientPreprocessedTransaction::observe(PatientPreprocessedTransactionObserver::class);
        PatientTransaction::observe(PatientTransactionObserver::class);
        PatientVisitDiagnose::observe(PatientVisitDiagnoseObserver::class);
        SquareTransaction::observe(SquareTransactionObserver::class);
        PatientSquareAccountCard::observe(PatientSquareAccountCardObserver::class);
        PatientSquareAccount::observe(PatientSquareAccountObserver::class);
        PatientTransactionAdjustment::observe(PatientTransactionAdjustmentObserver::class);
        PatientDiagnose::observe(PatientDiagnoseObserver::class);

        Validator::extend('allow_select_provider', function ($attribute, $value, $parameters, $validator) {
            $valid = !User::query()->where('provider_id', $value)
                ->join('users_meta', 'users_meta.user_id', '=', 'users.id')
                ->where('users_meta.has_access_rights_to_reassign_page', false)
                ->exists();

            return $valid;
        });

        Event::listen(Authenticated::class, function ($event) {
            $menuApiRepository = app(MenuApiRepositoryInterface::class);
            $menu = $menuApiRepository->getMenuData();
            view()->share('menu_links', $menu);
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment() !== 'production') {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }
    }

    private function setMysqlTimezone()
    {
        $offset = Carbon::now()->offsetHours;
        $isNegative = $offset < 0;
        $offset = abs($offset);
        if($offset < 10) {
            $offset = '0' . $offset;
        }
        $offset .= ':00';
        if($isNegative) {
            $offset = '-' . $offset;
        } else {
            $offset = '+' . $offset;
        }
        config(['database.connections.mysql.timezone' => $offset]);
    }
}
