<?php

namespace App\Jobs\Officeally;

use App\Exceptions\Officeally\OfficeallyAuthenticationException;
use App\Exceptions\Officeally\OfficeallyException;
use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use App\Jobs\Parsers\Guzzle\PaymentsParser;
use App\Jobs\Patients\CalculatePatientBalance;
use App\Models\Officeally\OfficeallyTransaction;
use App\Option;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class MakePosting implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    /**
     * @var array
     */
    private $transactionIds;
    private $minDate;
    private $maxDate;
    
    /**
     * MakePosting constructor.
     *
     * @param array $transactionIds
     * @param       $minDate
     * @param       $maxDate
     */
    public function __construct(array $transactionIds, $minDate, $maxDate)
    {
        $this->transactionIds = $transactionIds;
        $this->minDate = $minDate;
        $this->maxDate = $maxDate;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $officeAllyHelper = new OfficeAllyHelper(Option::OA_ACCOUNT_3);
        $patientIds = [];
        OfficeallyTransaction::query()
            ->whereIn('external_id', $this->transactionIds)
            ->each(function(OfficeallyTransaction $payment) use ($officeAllyHelper) {
                $patientIds[] = $payment->patient_id;
                $transactionPayload = [
                    'start_posting_date' => null,
                    'is_warning' => false,
                    'error_message' => null,
                ];
                try {
                    $isCreated = $officeAllyHelper->makePostingNewApporoach($payment);
                    if (!$isCreated) {
                        $transactionPayload['error_message'] = 'Manual intervention required.';
                        $transactionPayload['is_warning'] = true;
                    }
                } catch (OfficeallyAuthenticationException $e) { 
                    $transactionPayload['error_message'] = 'OfficeAlly authorization error';
                    $transactionPayload['is_warning'] = true;
                    \App\Helpers\SentryLogger::officeAllyCaptureException($e);
                } catch (OfficeallyException $e) {
                    $transactionPayload['error_message'] = $e->getHumanReadableMessage();
                    \App\Helpers\SentryLogger::officeAllyCaptureException($e);
                } catch (\Exception $e) {
                    \Log::error($e->getTraceAsString());
                    $transactionPayload['error_message'] = 'Manual intervention required.';
                    \App\Helpers\SentryLogger::officeAllyCaptureException($e);
                }
                $payment->update($transactionPayload);
            });
        
        \Bus::dispatchNow(new PaymentsParser($this->minDate, $this->maxDate, PaymentsParser::PAYER_PATIENT, PaymentsParser::ALL_PAYMENTS));

        $patientIds = array_unique($patientIds);
        if (count($patientIds)) {
            \Bus::dispatchNow(new CalculatePatientBalance($patientIds));
        }
    }
}
