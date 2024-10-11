<?php

namespace App\Providers;

use App\Models\Square\SquarePaymentMethod;
use App\Repositories\Appointment\Model\AppointmentRepository;
use App\Repositories\Appointment\Model\AppointmentRepositoryInterface;
use App\Repositories\Appointment\Model\TelehealthAppointmentRepository;
use App\Repositories\Appointment\Model\TelehealthAppointmentRepositoryInterface;
use App\Repositories\Appointment\Payment\AbstractPaymentRepository;
use App\Repositories\EligibilityPayer\EligibilityPayerRepository;
use App\Repositories\EligibilityPayer\EligibilityPayerRepositoryInterface;
use App\Repositories\Fax\FaxRepository;
use App\Repositories\Fax\FaxRepositoryInterface;
use App\Repositories\MenuApiForReactLayouts\MenuApiRepository;
use App\Repositories\MenuApiForReactLayouts\MenuApiRepositoryInterface;
use App\Repositories\NewPatientsCRM\PatientInquiry\PatientInquiryRepository;
use App\Repositories\NewPatientsCRM\PatientInquiry\PatientInquiryRepositoryInterface;
use App\Repositories\NewPatientsCRM\PatientInquirySource\PatientInquirySourceRepository;
use App\Repositories\NewPatientsCRM\PatientInquirySource\PatientInquirySourceRepositoryInterface;
use App\Repositories\NewPatientsCRM\PatientInquiryStage\PatientInquiryStageRepository;
use App\Repositories\NewPatientsCRM\PatientInquiryStage\PatientInquiryStageRepositoryInterface;
use App\Repositories\NewPatientsCRM\PatientLead\PatientLeadRepository;
use App\Repositories\NewPatientsCRM\PatientLead\PatientLeadRepositoryInterface;
use App\Repositories\NewPatientsCRM\PatientLeadComment\PatientLeadCommentRepository;
use App\Repositories\NewPatientsCRM\PatientLeadComment\PatientLeadCommentRepositoryInterface;
use App\Repositories\Patient\PatientNoteUnlockRequestRepository;
use App\Repositories\Patient\PatientNoteUnlockRequestRepositoryInterface;
use App\Repositories\Patient\PatientRemovalRequestRepository;
use App\Repositories\Patient\PatientRemovalRequestRepositoryInterface;
use App\Repositories\Office\OfficeRepository;
use App\Repositories\Office\OfficeRepositoryInterface;
use App\Repositories\OfficeRoom\OfficeRoomRepository;
use App\Repositories\OfficeRoom\OfficeRoomRepositoryInterface;
use App\Repositories\Patient\CheckChargeCancellationRepository;
use App\Repositories\Patient\CheckChargeCancellationRepositoryInterface;
use App\Repositories\Patient\PatientDocumentRepository;
use App\Repositories\Patient\PatientDocumentRepositoryInterface;
use App\Repositories\Patient\PatientRepository;
use App\Repositories\Patient\PatientRepositoryInterface;
use App\Repositories\PatientHasProvider\PatientHasProviderRepository;
use App\Repositories\PatientHasProvider\PatientHasProviderRepositoryInterface;
use App\Repositories\PatientInsurance\PatientInsuranceRepository;
use App\Repositories\PatientInsurance\PatientInsuranceRepositoryInterface;
use App\Repositories\PatientInsurancesProcedure\PatientInsuranceProcedureRepository;
use App\Repositories\PatientInsurancesProcedure\PatientInsuranceProcedureRepositoryInterface;
use App\Repositories\PatientTransfer\PatientTransferRepository;
use App\Repositories\PatientTransfer\PatientTransferRepositoryInterface;
use App\Repositories\Provider\Availability\ProviderAvailabilityRepository;
use App\Repositories\Provider\Availability\ProviderAvailabilityRepositoryInterface;
use App\Repositories\Provider\ProviderRepository;
use App\Repositories\Provider\ProviderRepositoryInterface;
use App\Repositories\Provider\Salary\BillingPeriodRepository;
use App\Repositories\Provider\Salary\BillingPeriodRepositoryInterface;
use App\Repositories\Provider\Salary\Timesheet\TimesheetRepository;
use App\Repositories\Provider\Salary\Timesheet\TimesheetRepositoryInterface;
use App\Repositories\Provider\SupervisorRepository;
use App\Repositories\Provider\SupervisorRepositoryInterface;
use App\Repositories\ReauthorizationRequestDashboard\ReauthorizationRequestDashboardRepository;
use App\Repositories\ReauthorizationRequestDashboard\ReauthorizationRequestDashboardRepositoryInterface;
use App\Repositories\Ringcentral\FaxLoggingRepository;
use App\Repositories\Ringcentral\FaxLoggingRepositoryInterface;
use App\Repositories\Ringcentral\FaxRingcentralRepository;
use App\Repositories\Ringcentral\FaxRingcentralRepositoryInterface;
use App\Repositories\Ringcentral\PatientRingcentralRepository;
use App\Repositories\Ringcentral\PatientRingcentralRepositoryInterface;
use App\Repositories\Ringcentral\RingcentralRepository;
use App\Repositories\Ringcentral\RingcentralRepositoryInterface;
use App\Repositories\SecretariesDashboard\ImportantForToday\ImportantForTodayRepository;
use App\Repositories\SecretariesDashboard\ImportantForToday\ImportantForTodayRepositoryInterface;
use App\Repositories\SecretariesDashboard\NewLostPatients\NewLostPatientsRepository;
use App\Repositories\SecretariesDashboard\NewLostPatients\NewLostPatientsRepositoryInterface;
use App\Repositories\Square\ApiRepository as SquareApiRepository;
use App\Repositories\Square\ApiRepositoryInterface as SquareApiRepositoryInterface;
use App\Repositories\Statistics\StatisticsRepository;
use App\Repositories\Statistics\StatisticsRepositoryInterface;
use App\Repositories\UpdateNotification\UpdateNotificationRepository;
use App\Repositories\UpdateNotification\UpdateNotificationRepositoryInterface;
use App\Repositories\UpdateNotificationSubstitution\UpdateNotificationSubstitutionRepository;
use App\Repositories\UpdateNotificationSubstitution\UpdateNotificationSubstitutionRepositoryInterface;
use App\Repositories\UpdateNotificationTemplate\UpdateNotificationTemplateRepository;
use App\Repositories\UpdateNotificationTemplate\UpdateNotificationTemplateRepositoryInterface;
use App\Repositories\Patient\PatientDocumentRequestRepository;
use App\Repositories\Patient\PatientDocumentRequestRepositoryInterface;
use App\Repositories\Patient\PatientDocZipGenerate\PatientDocZipGenerateRepository;
use App\Repositories\Patient\PatientDocZipGenerate\PatientDocZipGenerateRepositoryInterface;
use App\Repositories\Patient\PreprocessedTransactionRepository;
use App\Repositories\Patient\PreprocessedTransactionRepositoryInterface;
use App\Repositories\Provider\Comments\ProviderCommentRepository;
use App\Repositories\Provider\Comments\ProviderCommentRepositoryInterface;
use App\Repositories\TreatmentModality\TreatmentModalityRepository;
use App\Repositories\TreatmentModality\TreatmentModalityRepositoryInterface;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
    
    }
    
    /**
     * @return void
     */
    public function register()
    { 
        $this->app->bind(FaxLoggingRepositoryInterface::class, FaxLoggingRepository::class);
        $this->app->bind(PatientRingcentralRepositoryInterface::class, PatientRingcentralRepository::class);
        $this->app->bind(FaxRingcentralRepositoryInterface::class, FaxRingcentralRepository::class);
        $this->app->bind(SquareApiRepositoryInterface::class, SquareApiRepository::class);
        $this->app->bind(ProviderRepositoryInterface::class, ProviderRepository::class);
        $this->app->bind(PatientRepositoryInterface::class, PatientRepository::class);
        $this->app->bind(OfficeRepositoryInterface::class, OfficeRepository::class);
        $this->app->bind(AppointmentRepositoryInterface::class, AppointmentRepository::class);
        $this->app->bind(TelehealthAppointmentRepositoryInterface::class, TelehealthAppointmentRepository::class);
        $this->app->bind(OfficeRoomRepositoryInterface::class, OfficeRoomRepository::class);
        $this->app->bind(EligibilityPayerRepositoryInterface::class, EligibilityPayerRepository::class);
        $this->app->bind(PatientInsuranceProcedureRepositoryInterface::class, PatientInsuranceProcedureRepository::class);
        $this->app->bind(PatientInsuranceRepositoryInterface::class, PatientInsuranceRepository::class);
        $this->app->bind(TimesheetRepositoryInterface::class, TimesheetRepository::class);
        $this->app->bind(BillingPeriodRepositoryInterface::class, BillingPeriodRepository::class);
        $this->app->bind(RingcentralRepositoryInterface::class, RingcentralRepository::class);
        $this->app->bind(MenuApiRepositoryInterface::class, MenuApiRepository::class);
        $this->app->bind(CheckChargeCancellationRepositoryInterface::class, CheckChargeCancellationRepository::class);

        $this->app->bind(AbstractPaymentRepository::class, function ($app) {
            /** @var Request $request */
            $request = app('request');
            $paymentMethodId = $request->request->get('payment_method_id');
            $paymentMethods = SquarePaymentMethod::all();
            $paymentMethod = $paymentMethods->where('id', $paymentMethodId)->first();
            if ($paymentMethodId === null || !$paymentMethod) {
                abort(422, trans('validation.in', ['attribute' => 'payment_method_id']));
            }
            $paymentMethods = $paymentMethods->pluck('slug', 'id');
            $classPrefix = studly_case($paymentMethods[$paymentMethodId]);

            $repositoryClassFullName = "\\App\\Repositories\\Appointment\\Payment\\{$classPrefix}PaymentRepository";
            if($classPrefix === null || !class_exists($repositoryClassFullName)) {
                abort(400, 'Invalid Repository Class: ' . $repositoryClassFullName);
            }

            return new $repositoryClassFullName($paymentMethod, $request->route('patient'), $request->route('appointment'), $request->all());
        });
        $this->app->bind(PatientDocumentRepositoryInterface::class, PatientDocumentRepository::class);
        $this->app->bind(UpdateNotificationRepositoryInterface::class, UpdateNotificationRepository::class);
        $this->app->bind(UpdateNotificationTemplateRepositoryInterface::class, UpdateNotificationTemplateRepository::class);
        $this->app->bind(UpdateNotificationSubstitutionRepositoryInterface::class, UpdateNotificationSubstitutionRepository::class);
        $this->app->bind(ProviderAvailabilityRepositoryInterface::class, ProviderAvailabilityRepository::class);
        $this->app->bind(StatisticsRepositoryInterface::class, StatisticsRepository::class);
        $this->app->bind(ImportantForTodayRepositoryInterface::class, ImportantForTodayRepository::class);
        $this->app->bind(NewLostPatientsRepositoryInterface::class, NewLostPatientsRepository::class);
        $this->app->bind(PatientLeadRepositoryInterface::class, PatientLeadRepository::class);
        $this->app->bind(PatientLeadCommentRepositoryInterface::class, PatientLeadCommentRepository::class);
        $this->app->bind(PatientInquiryRepositoryInterface::class, PatientInquiryRepository::class);
        $this->app->bind(PatientInquiryStageRepositoryInterface::class, PatientInquiryStageRepository::class);
        $this->app->bind(PatientInquirySourceRepositoryInterface::class, PatientInquirySourceRepository::class);
        $this->app->bind(FaxRepositoryInterface::class, FaxRepository::class);
        $this->app->bind(SupervisorRepositoryInterface::class, SupervisorRepository::class);
        $this->app->bind(ReauthorizationRequestDashboardRepositoryInterface::class, ReauthorizationRequestDashboardRepository::class);
        $this->app->bind(PatientDocumentRequestRepositoryInterface::class, PatientDocumentRequestRepository::class);
        $this->app->bind(PreprocessedTransactionRepositoryInterface::class, PreprocessedTransactionRepository::class);
        $this->app->bind(PatientDocZipGenerateRepositoryInterface::class, PatientDocZipGenerateRepository::class);
        $this->app->bind(PatientRemovalRequestRepositoryInterface::class, PatientRemovalRequestRepository::class);
        $this->app->bind(PatientNoteUnlockRequestRepositoryInterface::class, PatientNoteUnlockRequestRepository::class);
        $this->app->bind(PatientTransferRepositoryInterface::class, PatientTransferRepository::class);
        $this->app->bind(PatientHasProviderRepositoryInterface::class, PatientHasProviderRepository::class);
        $this->app->bind(ProviderCommentRepositoryInterface::class, ProviderCommentRepository::class);
        $this->app->bind(TreatmentModalityRepositoryInterface::class, TreatmentModalityRepository::class);
    }
}
