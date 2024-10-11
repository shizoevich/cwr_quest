<?php

namespace App\Helpers\Sites\OfficeAlly\Traits;

use App\Models\Officeally\OfficeallyLog;
use App\Models\Officeally\OfficeallyTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Symfony\Component\DomCrawler\Crawler;
use voku\helper\HtmlDomParser;
/**
 * Trait Payments
 * @package App\Helpers\Sites\OfficeAlly\Traits
 */
trait Payments
{

    /**
     * @param      $payerTypeId
     * @param      $paymentType
     * @param null $dateFrom
     * @param null $dateTo
     *
     * @return mixed
     */
    public function getPayments($payerTypeId, $paymentType, $dateFrom = null, $dateTo = null)
    {
        $urlFilter = [
            'rules' => [
                [
                    'field' => 'MaxRecords',
                    'data' => 1000000,
                ],
                [
                    'field' => 'PayerTypeID',
                    'data' => $payerTypeId,
                ],
                [
                    'field' => 'PaymentType',
                    'data' => $paymentType,
                ],
            ],
        ];
        if (!is_null($dateFrom)) {
            $urlFilter['rules'][] = [
                'field' => 'StartDate',
                'data' => $dateFrom->format('m/d/Y'),
            ];
        }
        if (!is_null($dateTo)) {
            $urlFilter['rules'][] = [
                'field' => 'EndDate',
                'data' => $dateTo->format('m/d/Y'),
            ];
        }

        $uri = 'Accounting/ReceivedPayments.aspx?Tab=R&jqGridID=ctl00_phFolderContent_myCustomGrid_myGrid&_search=true&rows=20&page=1&sidx=&sord=&filters2=' . urlencode(json_encode($urlFilter,
            JSON_UNESCAPED_SLASHES));

        $response = $this->officeAlly->get($uri, [], true);
        $payments = json_decode($response->getBody()->getContents(), true);
        if ($payments === null) {
            $this->officeAlly->notifyIfFailed('Payment list is not parsed.');
        }
        $payments = data_get($payments, 'rows');

        return $payments;
    }

    /**
     * @param      $appliedType
     * @param null $dateFrom
     * @param null $dateTo
     *
     * @return mixed
     */
    public function getAppliedPayments($appliedType, $dateFrom = null, $dateTo = null)
    {
        $urlFilter = [
            'rules' => [
                [
                    'field' => 'MaxRecords',
                    'data' => '1000000',
                ],
                [
                    'field' => 'OfficeBy',
                    'data' => 'payment',
                ],
                [
                    'field' => 'AppliedType',
                    'data' => $appliedType,
                ],
            ],
        ];

        if (!is_null($dateFrom) && !is_null($dateTo)) {
            $urlFilter['rules'] = array_merge(
                $urlFilter['rules'],
                [
                    [
                        'field' => 'SearchBy',
                        'data' => '6',
                    ],
                    [
                        'field' => 'SearchCondition',
                        'data' => '6',
                    ],
                    [
                        'field' => 'StartDate',
                        'data' => $dateFrom->format('m/d/Y'),
                    ],
                    [
                        'field' => 'EndDate',
                        'data' => $dateTo->copy()->endOfDay()->format('m/d/Y g:i A'),
                    ]
                ]
            );
        }

        $uri = 'Accounting/AppliedPayments.aspx?Tab=R&jqGridID=ctl00_phFolderContent_myCustomGrid_myGrid&_search=true&rows=20&page=1&sidx=&sord=&filters2=' . urlencode(json_encode($urlFilter, JSON_UNESCAPED_SLASHES));
        
        $response = $this->officeAlly->get($uri, [], true);
        $payments = json_decode($response->getBody()->getContents(), true);
        if ($payments === null) {
            $message = 'Applied payment list is not parsed';
            if (!is_null($dateFrom) && !is_null($dateTo)) {
                $message = $message . " ({$dateFrom->format('m/d/Y')} - {$dateTo->format('m/d/Y')})";
            }
            $this->officeAlly->notifyIfFailed($message);
        }
        $payments = data_get($payments, 'rows');

        return $payments;
    }

    /**
     * @param int $appointmentId
     *
     * @return mixed
     */
    public function getAppointmentPayments(int $appointmentId)
    {
        $response = $this->officeAlly->get("Appointments/CheckIn.aspx?AID={$appointmentId}&mode=add&jqGridID=myCustomGrid_myGrid&_search=false&rows=2000&page=1&sidx=&sord=asc", [
            'headers' => [
                'Accept' => 'application/json',
            ],
        ], true);
        $payments = json_decode($response->getBody()->getContents(), true);
        if (empty($payments) && !is_null($payments)) {
            return [];
        }
        $payments = data_get($payments, 'rows');
        if ($payments === null) {
            $this->officeAlly->notifyIfFailed("Payment list for appointment {$appointmentId} is not parsed.");
        }

        return $payments;
    }

    /**
     * @return mixed
     */
    public function getBillingProviders()
    {
        $response = $this->officeAlly->get('SharedFiles/popup/Popup.aspx?name=BillingProvider', [], true);

        return $response->getBody()->getContents();
    }

    /**
     * @param OfficeallyTransaction $payment
     *
     * @return bool
     */
    /* old version dont work*/
    public function makePosting(OfficeallyTransaction $payment)
    {
        if (!$this->isProduction()) {
            return true;
        }
        $page = $this->officeAlly->get("Accounting/ApplyPayment.aspx?Tab=R&PageAction=apply&PID={$payment->external_id}&rnum=200", [], true)->getBody()->getContents();
        $crawler = new Crawler($page);

        $this->log(OfficeallyLog::ACTION_POSTING, true, ['payment_id' => $payment->external_id], $crawler);

        $unappliedAmount = (float) $crawler->filter('#ctl00_phFolderContent_UnappliedAmount')->first()->attr('value');

        $this->log(OfficeallyLog::ACTION_POSTING, true, ['payment_id' => $payment->external_id], $unappliedAmount);

        if ($unappliedAmount <= 0) {
            $this->log(OfficeallyLog::ACTION_POSTING, true, ['payment_id' => $payment->external_id], 'Unapplied Amount = ' . $unappliedAmount);
            return true;
        }
        $paymentDate = trim($crawler->filter('#ctl00_phFolderContent_PaymentDate')->first()->attr('value'));
        if ($paymentDate) {
            $paymentDate = Carbon::parse($paymentDate)->toDateString();
        }
        $payload = [];

        $crawler->filter('#aspnetForm input')->each(function ($node) use (&$payload) {
            $inputType = $node->attr('type');
            $inputName = $node->attr('name');
            if ($inputType !== 'button' && $inputType !== 'submit' && !empty($inputName)) {
                $payload[$inputName] = $this->prepareValue($node->attr('value'));
            }
        });

        $payload['__EVENTTARGET'] = '';
        $payload['__EVENTARGUMENT'] = '';
        $payload['__SCROLLPOSITIONX'] = '0';
        $payload['__SCROLLPOSITIONY'] = '0';
        $payload['lstPaymentAccount'] = '101,-';
        $payload['lstAdjustmentAccount'] = '200,-';

        $payload['ctl00$phFolderContent$VisitID'] = '';
        $payload['ctl00$phFolderContent$hdnMode'] = 'Apply';
        $payload['ctl00$phFolderContent$btnApply2'] = 'Apply';
        $payload['ctl00$phFolderContent$PatientID'] = $crawler->filter('#ctl00_phFolderContent_PatientID')->first()->attr('value');
        $payload['ctl00$phFolderContent$hdnPatientID'] = $crawler->filter('#ctl00_phFolderContent_PatientID')->first()->attr('value');

        $visits = [];

        $crawler->filter('#ctl00_phFolderContent_divSearchResults2 table tbody tr.text')->each(function ($node) use (&$visits) {
            $visits[] = [
                'dos_id' => $node->filter('input[id^=DOS]')->first()->attr('id'),
                'dos_value' => Carbon::parse($node->filter('input[id^=DOS]')->first()->attr('value'))->toDateString(),
                'charge_id' => $node->filter('input[id^=Charge]')->first()->attr('id'),
                'charge_value' => (float) $node->filter('input[id^=Charge]')->first()->attr('value'),
                'balance_id' => $node->filter('input[id^=Remainder]')->first()->attr('id'),
                'balance_value' => (float) $node->filter('input[id^=Remainder]')->first()->attr('value'),
                'payment_id' => $node->filter('input[id^=Payment]')->first()->attr('id'),
            ];
        });

        //all logic copied from old functionality
        $visits = array_reverse($visits);
        $dataChanged = false;
        foreach ($visits as $visit) {
            if ($visit['balance_value'] <= 0) {
                continue;
            }
            if ($paymentDate === $visit['dos_value'] && ($visit['balance_value'] === $unappliedAmount || ($visit['charge_value'] - $visit['balance_value']) == 0)) {
                $dataChanged = true;
                $payload[$visit['payment_id']] = (string) ($unappliedAmount * -1);
                break;
            }
        }
        if (!$dataChanged) {
            foreach ($visits as $visit) {
                if ($visit['balance_value'] <= 0) {
                    continue;
                }
                if ($visit['balance_value'] === $unappliedAmount) {
                    $dataChanged = true;
                    $payload[$visit['payment_id']] = (string) ($unappliedAmount * -1);
                    break;
                }
            }
        }

        if (!$dataChanged) {
            foreach ($visits as $visit) {
                if ($visit['balance_value'] <= 0) {
                    continue;
                }
                if (($visit['charge_value'] - $visit['balance_value']) == 0) {
                    $dataChanged = true;
                    $payload[$visit['payment_id']] = (string) ($unappliedAmount * -1);
                    break;
                }
            }
        }
 
        if ($dataChanged) {
            $payload['ctl00$phFolderContent$UnappliedAmount'] = '0.00';
            $response = $this->officeAlly->post("Accounting/ApplyPayment.aspx?Tab=R&PageAction=apply&PID={$payment->external_id}&rnum=20", [
                'form_params' => $payload,
            ], true)->getBody()->getContents();
            $crawler = new Crawler($response);
            try {
                $statusMessage = $crawler->filter('#ctl00_phFolderContent_lblStatus font')->first()->text();

            } catch(InvalidArgumentException $e) {
                $statusMessage = $e->getMessage(); 
            } 
            $success = false;
            if ($statusMessage === 'Successfully applied.') {
                $success = true;
            }
            $this->log(OfficeallyLog::ACTION_POSTING, $success, ['payment_id' => $payment->external_id], $statusMessage);

            return $success;
        } else {
            $this->log(OfficeallyLog::ACTION_POSTING, false, ['payment_id' => $payment->external_id], 'Cannot make posting automatically.');

            return false;
        }
    }

    /**
     * @param OfficeallyTransaction $payment
     *
     * @return bool
     */
    public function makePostingNewApporoach(OfficeallyTransaction $payment)
    {
        if (!$this->isProduction()) {
            return true;
        }
        $page = $this->officeAlly->get("Accounting/ApplyPayment.aspx?Tab=R&PageAction=apply&PID={$payment->external_id}&rnum=200", [], true)->getBody()->getContents();

        $crawler = HtmlDomParser::str_get_html($page);

        $this->log(OfficeallyLog::ACTION_POSTING, true, ['payment_id' => $payment->external_id], $crawler);

        $unappliedAmount = (float) preg_replace('/[^0-9\.]/', '', $crawler->findOne('#ctl00_phFolderContent_UnappliedAmount')->text());

        $this->log(OfficeallyLog::ACTION_POSTING, true, ['payment_id' => $payment->external_id], $unappliedAmount);

        if ($unappliedAmount <= 0) {
            $this->log(OfficeallyLog::ACTION_POSTING, true, ['payment_id' => $payment->external_id], 'Unapplied Amount = ' . $unappliedAmount);
            return true;
        }

        $paymentDate = trim($crawler->findOne('#ctl00_phFolderContent_PaymentDate')->text());

        if ($paymentDate) {
            $paymentDate = Carbon::parse($paymentDate)->toDateString();
        }

        $payload = [];

        foreach ($crawler->find('input') as $node) {
            $inputType = $node->type;
            $inputName = $node->name;
            if ($inputType !== 'button' && $inputType !== 'submit' && !empty($inputName)) {
                $payload[$inputName] = $this->prepareValue($node->value);
            }
        }

        $payload['__EVENTTARGET'] = '';
        $payload['__EVENTARGUMENT'] = '';
        $payload['__SCROLLPOSITIONX'] = '0';
        $payload['__SCROLLPOSITIONY'] = '0';
        $payload['lstPaymentAccount'] = '101,-';
        $payload['lstAdjustmentAccount'] = '200,-';

        $payload['ctl00$phFolderContent$VisitID'] = '';
        $payload['ctl00$phFolderContent$hdnMode'] = 'Apply';
        $payload['ctl00$phFolderContent$btnApply2'] = 'Apply';
        $payload['ctl00$phFolderContent$PatientID'] = $crawler->find('#ctl00_phFolderContent_PatientID', 0)->value;
        $payload['ctl00$phFolderContent$hdnPatientID'] = $crawler->find('#ctl00_phFolderContent_PatientID', 0)->value;

        $visits = [];
        $nodeCount = 1;
        foreach ($crawler->find('#ctl00_phFolderContent_divSearchResults2 table tbody tr.text') as $node) {

            $nodeNumber['nodeCount'] = $nodeCount++;

            $dosId = $node->findOne('#DOS' . $nodeNumber['nodeCount'])->id;
            $dosValue = Carbon::parse($node->findOne('#DOS' . $nodeNumber['nodeCount'])->text())->toDateString();
            $chargeId = $node->findOne('#Charge' . $nodeNumber['nodeCount'])->id;
            $chargeValue = (float) preg_replace('/[^0-9\.]/', '', $node->findOne('#Charge' . $nodeNumber['nodeCount'])->text());
            $balanceId = $node->findOne('#Remainder' . $nodeNumber['nodeCount'])->id;
            $balanceValue = (float) preg_replace('/[^0-9\.]/', '', $node->findOne('#Remainder' . $nodeNumber['nodeCount'])->text());
            $paymentId = $node->findOne('#Payment' . $nodeNumber['nodeCount'])->id;

            $visits[] = [
                'dos_id' => $dosId,
                'dos_value' => $dosValue,
                'charge_id' => $chargeId,
                'charge_value' => $chargeValue,
                'balance_id' => $balanceId,
                'balance_value' => $balanceValue,
                'payment_id' => $paymentId,
            ];
        }

        //all logic copied from old functionality
        $visits = array_reverse($visits);

        $dataChanged = false;
        foreach ($visits as $visit) {

            if ($visit['balance_value'] <= 0) {
                continue;
            }
            if ($paymentDate === $visit['dos_value'] && ($visit['balance_value'] === $unappliedAmount || ($visit['charge_value'] - $visit['balance_value']) == 0)) {
                $dataChanged = true;
                $payload[$visit['payment_id']] = (string) ($unappliedAmount * -1);
                break;
            }
        }
        if (!$dataChanged) {
            foreach ($visits as $visit) {
                if ($visit['balance_value'] <= 0) {
                    continue;
                }
                if ($visit['balance_value'] === $unappliedAmount) {
                    $dataChanged = true;
                    $payload[$visit['payment_id']] = (string) ($unappliedAmount * -1);
                    break;
                }
            }
        }

        if (!$dataChanged) {
            foreach ($visits as $visit) {
                if ($visit['balance_value'] <= 0) {
                    continue;
                }
                if (($visit['charge_value'] - $visit['balance_value']) == 0) {
                    $dataChanged = true;
                    $payload[$visit['payment_id']] = (string) ($unappliedAmount * -1);
                    break;
                }
            }
        }

        if ($dataChanged) {
            $payload['ctl00$phFolderContent$UnappliedAmount'] = '0.00';
            $response = $this->officeAlly->post("Accounting/ApplyPayment.aspx?Tab=R&PageAction=apply&PID={$payment->external_id}&rnum=20", [
                'form_params' => $payload,
            ], true)->getBody()->getContents();
            $crawler = HtmlDomParser::str_get_html($response);
            try {
                $statusMessage = $crawler->find('#ctl00_phFolderContent_lblStatus font', 0)->innertext;
            } catch (InvalidArgumentException $e) {
                $statusMessage = $e->getMessage();
            }
            $success = false;
            if ($statusMessage === 'Successfully applied.') {
                $success = true;
            }
            $this->log(OfficeallyLog::ACTION_POSTING, $success, ['payment_id' => $payment->external_id], $statusMessage);

            return $success;
        } else {
            $this->log(OfficeallyLog::ACTION_POSTING, false, ['payment_id' => $payment->external_id], 'Cannot make posting automatically.');

            return false;
        }
    }

    public function addNewPaymentForPatient(
        int $patientId,
        int $amount,
        int $transactionTypeId,
        string $comment
    ) {
        return $this->addNewPayment([
            'patient_id' => $patientId,
            'payment_date' => Carbon::now()->format('Y-m-d'),
            'payment_payer_type' => 'P',
            'payment_method_id' => $transactionTypeId,
            'description' => $comment,
            'payment_type_id' => 1,
            'payment_payer_id' => $patientId,
            'amount' => $amount,
            'comment' => $comment,
        ]);
    }

    public function addNewPayment(array $data = [])
    {
        $payload = [
            'PatientID' => $data['patient_id'] ?? 0,
            'AppointmentID' => $data['appointment_id'] ?? 0,
            'VisitID' => $data['visit_id'] ?? 0,
            'PaymentID' => $data['payment_id'] ?? 0,
            'InsuranceID' => $data['insurance_id'] ?? 0,
            'PaymentDate' => $data['payment_date'] ?? Carbon::now()->format('Y-m-d'),
            'PaymentPayerType' => $data['payment_payer_type'] ?? 'P', // "Payment from" field, default: Patient
            'PaymentMethodID' => $data['payment_method_id'] ?? 1, // Field below "Process payment", default: Cash
            'CheckNumber' => $data['check_number'] ?? '',
            'Description' => $data['description'] ?? '',
            'PaymentTypeID' => $data['payment_type_id'] ?? 1, // "Payment Method" field in form, default: Record Payment
            'PaymentPayerID' => $data['payment_payer_id'] ?? 0,
            'Amount' => $data['amount'] ?? 0,
            'CheckSource' => $data['check_source'] ?? '',
            'CheckIndexID' => $data['facility_id'] ?? 0,
            'BranchID' => $data['branch_id'] ?? 0,
            'OfficeID' => $data['office_id'] ?? 0,
            'ProviderID' => $data['provider_id'] ?? 0,
            'FacilityID' => $data['facility_id'] ?? 0,
            'IsAutopost' => $data['is_autopost'] ?? false,
            'Comments' => $data['comment'] ?? '',
        ];

        $response = $this->officeAlly->post(
            'https://pm.officeally.com/pm/commonusercontrols/ajax/webapi/webapihandler.ashx?action=/v1/oaPayment/recordPayment&ApiType=11', 
            ['json' => $payload]
        );

        return $response->getBody()->getContents();
    }

    public function deletePayment(int $paymentId)
    {
        $payload = [
            'oper' => 'del',
            'id' => $paymentId,
        ];

        $response = $this->officeAlly->post(
            'https://pm.officeally.com/pm/Accounting/ReceivedPayments.aspx?Tab=R&jqGridID=ctl00_phFolderContent_myCustomGrid_myGrid&editMode=1',
            ['form_params' => $payload]
        );

        return $response->getBody()->getContents();
    }
}
