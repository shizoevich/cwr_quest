<?php

namespace App\Console;

use App\Console\Commands\Cache\ClearCache;
use App\Console\Commands\CalculateProvidersStatisticsByMonths;
use App\Console\Commands\CheckLateCancellationPayment;
use App\Console\Commands\CheckPasswordExpiration;
use App\Console\Commands\GetAppliedPaymentsByMonths;
use App\Console\Commands\RestartCreatingMissingVisits;
use App\Console\Commands\RingCentral\SyncCallStatuses;
use App\Console\Commands\SingleUse\AttachInitialAssessmentsToAppointments;
use App\Console\Commands\Tridiuum\AttachTridiuumPatients;
use App\Console\Commands\Tridiuum\GetAppointments;
use App\Console\Commands\Tridiuum\GetAppointmentsLong;
use App\Jobs\DeleteTmpAssessmentForms;
use App\Jobs\Google\CalendarEvent\CheckConferencesCreationStatus;
use App\Jobs\Google\CalendarEvent\GetConferenceCallLogs;
use App\Jobs\Parsers\Guzzle\AppointmentReminderStatusesParser;
use App\Jobs\Parsers\Guzzle\InsurancesParser;
use App\Jobs\Parsers\Guzzle\PatientVisitsParserForTimesheet;
use App\Jobs\RingCentral\RenewIfBlacklistedRingcentralWebhooks;
use App\Jobs\RingCentral\RenewWebhooks as RenewRingcentralWebhooks;
use App\Jobs\RingCentral\SyncCallFaxes;
use App\Jobs\Salary\AssignVisitsToAppointments;
use App\Jobs\Salary\SyncSalaryData;
use App\Jobs\SendScheduledNotification;
use App\Jobs\Tridiuum\GetPatients;
use App\Jobs\Tridiuum\TridiuumChatBot;
use App\Option;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\Parsers\RunParser::class,
        Commands\InstallNextCloudCommand::class,
        Commands\DeleteUnknownDocuments::class,
        Commands\DownloadHarassmentCertificatesFromAws::class,
        Commands\RingCentral\SyncCallFaxes::class,
        Commands\CreateRingcentralWebhook::class,
        Commands\Parsers\RunPatientProfileParser::class,
        Commands\Parsers\StatusChecker::class,
        Commands\PatientStatuses\SetActivePatientStatus::class,
        Commands\PatientStatuses\SetInactivePatientStatus::class,
        Commands\PatientStatuses\SetLostPatientStatus::class,
        Commands\PatientStatuses\UpdatePatientsStatus::class,
        Commands\UpdateProviderInsurances::class,
        Commands\Parsers\RunPatientAlertsParser::class,
        Commands\Parsers\RunPatientVisitsParser::class,
        Commands\SyncPatientNoteWithProvider::class,
        Commands\AddProviderRoleForRegisteredUsers::class,
        Commands\FillNoteInPaper::class,
        Commands\SyncProviderAvailability::class,
        Commands\FindPatientSquareAccount::class,
        Commands\Parsers\RunPatientTransactionsParser::class,
        Commands\Parsers\RunSquareCatalogItemsParser::class,
        Commands\GetUnassignedSquareCustomers::class,
        Commands\Patients\Balance\Reset::class,
        Commands\Square\GetCustomersData::class,
        Commands\Google\AssociateEhrUsersWithGoogleUsers::class,
        Commands\CreateLogDump::class,
        AttachInitialAssessmentsToAppointments::class,
        Commands\Tridiuum\GetAppointments::class,
        Commands\Tridiuum\GetAppointmentsLong::class,
        Commands\Tridiuum\SyncAvailabilities::class,
        Commands\Tridiuum\DeleteMissingDocuments::class,
        Commands\Tridiuum\GetPatientsData::class,
        Commands\Tridiuum\Check2faCredentials::class,
        Commands\RingCentral\SyncLogs::class,
        Commands\RingCentral\SyncCallStatuses::class,
        Commands\RingCentral\SyncInProgressCallStatuses::class,
        Commands\FixAppointmentStatus::class,
        Commands\AssignAppointmentsToProgressNotes::class,
        Commands\MarkAppointmentsAsInitial::class,
        Commands\DeleteProgressNotesWithEqDOS::class,
        Commands\Parsers\ChangeOfficeAllyPassword::class,
        Commands\Tridiuum\ChangeTridiuumPassword::class,
        Commands\Parsers\RunPatientProfileParserOnlyForCompletedAppt::class,
        Commands\AssignVisitsToAppointments::class,
        Commands\GenerateBillingPeriods::class,
        Commands\SingleUse\DeleteComments::class,
        Commands\SingleUse\SyncPatientDiagnoses::class,
        Commands\SingleUse\SyncPatientNoteDiagnoses::class,
        Commands\SingleUse\SyncInitialAssessmentDiagnoses::class,
        Commands\SingleUse\FixGoogleCallLogsTimezone::class,
        Commands\SingleUse\FixSalary::class,
        Commands\SingleUse\TmpRunPatientProfileParser::class,
        Commands\SingleUse\ClearMissingNotesStatistic::class,
        Commands\SingleUse\SanitizePatientPhone::class,
        Commands\SingleUse\SanitizePatientLeadPhone::class,
        Commands\SingleUse\SyncSalaryTimesheetVisits::class,
        Commands\SingleUse\CreateInsuranceUser::class,
        Commands\SingleUse\SyncRingcentralCallLogs::class,
        Commands\SingleUse\CheckPostingSalary::class,
        Commands\RestartCreatingMissingVisits::class,
        Commands\SendScheduledNotifications::class,
        Commands\QueueSize::class,
        Commands\CheckPasswordExpiration::class,
        Commands\GenerateGmailApiToken::class,
        Commands\Cache\ClearCache::class,
        Commands\GoogleDrive\CopyFaxes::class,
        Commands\GoogleDrive\CopyPatientDocuments::class,
        Commands\GoogleDrive\CopyPatientForms::class,
        Commands\GoogleDrive\CopyUsersMetaSignatures::class, 
        Commands\GoogleDrive\CopyProgressNotes::class, 
        Commands\GoogleDrive\CopyPatientAssessmentForms::class, 
        Commands\SingleUse\CheckLostVisits::class, 
        Commands\SetSickHoursPerYear::class,
        Commands\SetOption::class,
        Commands\ParseCancelFee::class,
        Commands\ParseConsentInfo::class,
        Commands\UpdateCancelFee::class,
        Commands\CalculateProvidersStatistics::class,
        Commands\CalculateProvidersStatisticsByMonths::class,
        Commands\GetAppliedPaymentsByMonths::class,
        Commands\SingleUse\DublicateCreatedAtForMandrillEvent::class,
        Commands\SingleUse\SyncPatientsPrimaryInsuranceId::class,
        CheckLateCancellationPayment::class,
        Commands\SingleUse\CheckAlertPost::class,
        Commands\Parsers\TestOfficeAllyLogin::class,
        Commands\SingleUse\TridiuumTrackCheck::class,
        Commands\SingleUse\OfficeAlly\CreatePatient::class,
        Commands\SingleUse\OfficeAlly\CreateAppointment::class,
        Commands\SingleUse\OfficeAlly\CreateAppointmentsForPeriod::class,
        Commands\SingleUse\UnserializeJob::class,
        Commands\SingleUse\UpdateScheduledNotificationsTarget::class,
        Commands\SingleUse\SetVisitCreatedStatuses::class,
        Commands\Tridiuum\AttachTridiuumDocs::class,
        Commands\Tridiuum\AttachTridiuumPatients::class,
        Commands\CloseCompletedInquiries::class,
        Commands\Salary\UpdateBilling::class,
        Commands\Salary\SoftDeleteProvider::class,
        Commands\SingleUse\SyncDateOfBirthForProgressNotesFromPatients::class,
        Commands\SingleUse\ResavePatientNoteDocumentsForTriWest::class,
        Commands\SingleUse\ResavePatientNoteDocumentsThatLessThanHour::class,
        Commands\SingleUse\ResaveDischargeSummaryDocuments::class,
        Commands\SingleUse\ResaveInitialAssessmentDocuments::class,
        Commands\SingleUse\RestoreActivePatientsWithFourAppointments::class,
        Commands\SingleUse\SetUserIdForOldSquareTransactions::class,
        Commands\SyncAttachedPatientsToSupervisor::class,
        Commands\CheckPhoneNumber::class,
        Commands\ExcelReport\GenerateProgressNotesExcelReport::class,
        Commands\ExcelReport\GeneratePaymentAvailabilityExcelReport::class,
        Commands\ExcelReport\GenerateLucetPatientVisitsExcelReport::class,
        Commands\SyncRelatedColumnsInReauthorizationForms::class,
        Commands\PatientStatuses\SetColumnStatusUpdatedAt::class,
        Commands\PatientStatuses\SetColumnStatusUpdatedAtForOldRecords::class,
        Commands\PatientStatuses\FixOutdatedActiveStatuses::class,
        Commands\PatientDocsZipArchive\ClearZipArchiveStorage::class,
        Commands\Salary\SyncTimesheetWithVisits::class,
        Commands\Salary\SyncTimesheetWithLateCancellations::class,
        Commands\SingleUse\SyncIsNewFlagForOldProviders::class,
        Commands\OfficeAlly\CompareDiagnoses::class,
        Commands\SalaryCheck\CompareOfficeAllyVisitsCount::class,
        Commands\SalaryCheck\CompareSalaryTimesheetVisitsAndSalary::class,
        Commands\SalaryCheck\CheckSalaryTimesheetVisitsDuplicates::class,
        Commands\SalaryCheck\CheckProvidersOvertime::class,
        Commands\SalaryCheck\CheckPatientsWithProgressNoteInsteadOfInitialAssessment::class,
        Commands\SingleUse\UpdateSuperviseeIdInPatientsHasProvidersTable::class,
        Commands\SingleUse\UpdateTreatmentModalityIdInPatientNotes::class,
        Commands\KaiserAudit\FindCancelledAppointments::class,
        Commands\KaiserAudit\FindVerticalProgressNotesDuplicates::class,
        Commands\KaiserAudit\FindHorizontalProgressNotesDuplicates::class,
        Commands\KaiserAudit\FindProgressNotesWithFakeSymbols::class,
        Commands\KaiserAudit\ResaveCallLogsThatLessThanHour::class,
        Commands\KaiserAudit\ResaveProgressNotesThatLessThanHour::class,
        Commands\KaiserAudit\UpdateFamilyAndCrisisProgressNotes::class,
        Commands\KaiserAudit\ParseCptCodeFromOfficeAlly::class,
        Commands\KaiserAudit\CheckSignatureInProgressNotes::class,
        Commands\KaiserAudit\UploadDocumentsToGoogleDrive::class,
        Commands\SingleUse\SyncMeetingIdInScheduledNotifications::class,
        Commands\SingleUse\SetPatientDeductibleFieldsFromAlerts::class,
        Commands\SingleUse\SetPatientSelfPayFieldsFromInsurance::class,
        Commands\StatisticsReport\GenerateNewPatientsReport::class,
        Commands\StatisticsReport\GenerateInsuranceStatisticsReport::class,
        Commands\StatisticsReport\GenerateNewPatientsInsuranceStatisticsReport::class,
        Commands\StatisticsReport\GenerateOneVisitWithProviderReport::class,
        Commands\StatisticsReport\Salary\GenerateCompletedInitialSurveyRecordsReport::class,
        Commands\StatisticsReport\Salary\GenerateCompletedSecondSurveyRecordsReport::class,
        Commands\StatisticsReport\GenerateSalaryGeneralStatisticsReport::class,
        Commands\StatisticsReport\Salary\GenerateActiveAppointmentsFromPreviousPeriodReport::class,
        Commands\StatisticsReport\Salary\GenerateInactiveOrLostPatientsFromPreviousPeriodReport::class,
        Commands\StatisticsReport\Salary\GenerateOnlineVisitsWithoutLogsFromPreviousPeriodReport::class,
        Commands\StatisticsReport\Salary\GenerateCancelledAppointmentsFromPreviousPeriodReport::class,
        Commands\StatisticsReport\Salary\GenerateOnlineVisitsWithCallLogsFromPreviousPeriodReport::class,
        Commands\StatisticsReport\Salary\GenerateVisitsWithoutCompletedFormsReport::class,
        Commands\StatisticsReport\Salary\GenerateVisitsWithNegativeBalanceReport::class,
        Commands\StatisticsReport\Salary\GenerateVisitsWithoutFutureAppointmentsReport::class,
        Commands\StatisticsReport\Salary\GenerateKaiserReferralsFillingReport::class,
        Commands\StatisticsReport\Salary\GeneratePatientsWithMissedAppointmentsReport::class,
        Commands\StatisticsReport\Salary\GenerateSalaryTotalsReport::class,
        Commands\StatisticsReport\GenerateLucetAppointmentsScheduledOnProvidersAvailabilityReport::class,
        Commands\StatisticsReport\GenerateAvailabilitySchedulingReport::class,
        Commands\StatisticsReport\GenerateTherapistsPlanCompletionReport::class,
    ];

    /**
     * Define the application's command schedule.
     * 
     * @param Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command(ClearCache::class)->dailyAt('00:00:15');

        // $schedule->job(new SendScheduledNotification(), 'workers-default')
        //     ->everyMinute();
        $schedule->job(new SendScheduledNotification())
            ->everyMinute();

        $schedule->job(new CheckConferencesCreationStatus(), 'workers-default')
            ->everyMinute();
        
        $schedule->job(new GetConferenceCallLogs(Carbon::now()->subHours(2)))
            ->cron('*/15 * * * *');
        
        $schedule->command(RestartCreatingMissingVisits::class)
            ->cron('*/10 * * * *');

        $schedule->job(new AssignVisitsToAppointments(Carbon::parse('2020-01-01')), 'workers-default')
            ->everyTenMinutes();
        
        $schedule->job(new \App\Jobs\Tridiuum\SyncAvailabilities(), 'tridiuum-availability')
            ->cron('*/20 * * * *');
        
        $schedule->command('parser:check-status')
            ->everyThirtyMinutes();

        $schedule->command('appointment-status:fix')
            ->hourly();

        //Get Tridiuum patient with documents
        $schedule->job(new GetPatients(true), 'tridiuum-parser')
            ->cron('0 */2 * * *');

        $schedule->command('parser:patient-transactions')
            ->everyTenMinutes();

        $schedule->command(GetAppointments::class)
            ->cron('*/20 * * * *');

        $schedule->command(GetAppointmentsLong::class)
            ->daily();
        
        $schedule->command(SyncCallStatuses::class)
            ->everyMinute();
    
        //@todo [OA parsers] uncomment when resolve problems with OA
//        $schedule->command('parser:run')
//            ->cron('*/20 * * * *');
        
        $schedule->job(new SyncSalaryData, 'workers-default')->everyThirtyMinutes();
    
        //@todo [OA parsers] uncomment when resolve problems with OA
//        $schedule->command('parser:run --parser=appointments --prev-days=60 --upcoming-days=90')
//            ->dailyAt('23:00');
        
        $schedule->command('billing-periods:generate')
            ->daily();
    
        //@todo [OA parsers] uncomment when resolve problems with OA
//        $schedule->command('parser:patient-alerts')
//            ->dailyAt('09:00');
        //@todo [OA parsers] uncomment when resolve problems with OA
//        $schedule->job(new DiagnosesParser)
//            ->daily();
        //@todo [OA parsers] uncomment when resolve problems with OA
//        $schedule->command('parser:patient-profile')
//            ->dailyAt('14:00');
        //@todo [OA parsers] uncomment when resolve problems with OA
//        $schedule->command('parser:patient-visits')
//            ->dailyAt('05:00'); //every 4 hours
        //@todo [OA parsers] uncomment when resolve problems with OA
//        $schedule->command('parser:patient-profile-for-completed-appt')
//            ->cron('59 */12 * * *');
    
        // run parser at 21:30 on Sunday
        $schedule->job(new InsurancesParser)
            ->cron('30 21 * * 0');
        //@todo [OA parsers] uncomment when resolve problems with OA
//        $schedule->job(new CPTCodesParser)
//            ->daily();
        //@todo [OA parsers] uncomment when resolve problems with OA
//        $schedule->job(new EligibilityPayersParser())
//            ->daily();

        $schedule->command('documents:delete_unknown')
            ->daily();

        $schedule->job(new DeleteTmpAssessmentForms())
            ->dailyAt('01:00');

        $schedule->command('dump:log')
            ->cron('0 0 * * 0');    // every Sunday
        
        $schedule->command(CheckPasswordExpiration::class)
            ->dailyAt('02:00');
    
        $schedule->job(new PatientVisitsParserForTimesheet(), 'daily-parser')->dailyAt('15:00');
        
        $schedule->job(new RenewRingcentralWebhooks())->daily();

        $schedule->job(new RenewIfBlacklistedRingcentralWebhooks())->hourly();

        $schedule->job(new TridiuumChatBot())->daily();

        $schedule->job(new SyncCallFaxes(1))->hourly();

        $this->scheduleCopyDocumentsToGoogleDrive($schedule);

        $schedule->command('copy:users_signatures')->daily();

        $schedule->command(AttachTridiuumPatients::class)
            ->dailyAt('02:50:00');

        $schedule->command(GetAppliedPaymentsByMonths::class)
            ->dailyAt('01:45:00');

        // $schedule->command('inquiries:close-completed')
        //     ->daily();

        $this->scheduleCalculateProvidersStatisticsByMonthsCommands($schedule);

        // $schedule->command('tridiuum:update-attach-docs')
        //     ->everyThirtyMinutes();

        // $this->scheduleAppointmentReminderStatusesParserJobs($schedule);

        $schedule->command('salary:soft-delete')
            ->cron('0 3 * * 1');

        $schedule->command('sync:attached-patients-to-supervisor')
            ->daily();
        
        $schedule->command('clear:zip-archive-storage')
            ->cron('0 2 * * 1');
    }

    protected function scheduleCalculateProvidersStatisticsByMonthsCommands(Schedule $schedule)
    {
        $schedule->command(CalculateProvidersStatisticsByMonths::class)
            ->dailyAt('02:45:00');

        // Update statistics for the previous 2 months at 03:45 on Monday, Wednesday, and Friday.
        $schedule->command(CalculateProvidersStatisticsByMonths::class, [
            '--start-date' => Carbon::today()->startOfMonth()->subMonths(2)->toDateString(),
            '--end-date' => Carbon::today()->startOfMonth()->subDay()->toDateString()
        ])->cron('45 3 * * 1,3,5');

        // Update statistics for the previous 5 months At 03:45 on Sunday
        $schedule->command(CalculateProvidersStatisticsByMonths::class, [
            '--start-date' => Carbon::today()->startOfMonth()->subMonths(5)->toDateString(),
            '--end-date' => Carbon::today()->startOfMonth()->subDay()->toDateString()
        ])->cron('45 3 * * 0');
    }

    protected function scheduleCopyDocumentsToGoogleDrive(Schedule $schedule)
    {
        $schedule->command('copy:faxes')
            ->cron('5 */1 * * *');
        $schedule->command('copy:patient_docs')
            ->cron('15 */1 * * *');
        $schedule->command('copy:patient_assessment_form')
            ->cron('25 */2 * * *');
        $schedule->command('copy:patient_progress_note')
            ->cron('35 */2 * * *');
        $schedule->command('copy:patient_forms')
            ->cron('45 */2 * * *');
    }

    protected function scheduleAppointmentReminderStatusesParserJobs(Schedule $schedule)
    {
        $schedule->job(new AppointmentReminderStatusesParser(Carbon::today(), null, Option::OA_ACCOUNT_1))
            ->cron('15 * * * *');
        $schedule->job(new AppointmentReminderStatusesParser(Carbon::today()->addDays(1), null, Option::OA_ACCOUNT_2))
            ->cron('35 */3 * * *');
        $schedule->job(new AppointmentReminderStatusesParser(Carbon::today()->addDays(2), null, Option::OA_ACCOUNT_3))
            ->cron('55 */6 * * *');
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands(): void
    {
        require base_path('routes/console.php');
    }
}
