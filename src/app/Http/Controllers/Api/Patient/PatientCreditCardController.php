<?php

namespace App\Http\Controllers\Api\Patient;

use App\Components\Square\Customer;
use App\Components\Square\CustomerCard;
use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\CreditCard\Charge;
use App\Http\Requests\Patient\CreditCard\Index;
use App\Jobs\Square\SyncCustomerData;
use App\Patient;
use App\Appointment;
use App\PatientSquareAccount;
use App\Models\Square\SquareCatalogItem;
use App\Models\Square\SquarePaymentMethod;
use App\Repositories\Appointment\Payment\CreditCardPaymentRepository;
use App\Status;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Square\Exceptions\ApiException;
use App\Exceptions\Square\SquareException;
use App\Exceptions\Officeally\Appointment\PaymentNotAddedException;

/**
 * Class PatientCreditCardController
 * @package App\Http\Controllers\Api\Patient
 */
class PatientCreditCardController extends Controller
{
    /**
     * @param Index   $request
     * @param Patient $patient
     *
     * @return JsonResponse
     */
    public function index(Index $request, Patient $patient)
    {
        $creditCards = [];
        $patient->squareAccounts()->with('cards')->each(function(PatientSquareAccount $squareAccount) use ($request, &$creditCards) {
            if ($request->input('force_sync')) {
                try {
                    \Bus::dispatchNow(new SyncCustomerData($squareAccount));
                    $squareAccount->refresh();
                } catch(ApiException $e) {
                    \Log::warning($e->getMessage());
                    \App\Helpers\SentryLogger::captureException($e);
                }
            }
            foreach ($squareAccount->cards as $card) {
                $creditCards[] = [
                    'id' => $card->getKey(),
                    'card_brand' => $card->card_brand,
                    'last_four' => $card->last_four,
                    'exp_month' => $card->exp_month,
                    'exp_year' => $card->exp_year,
                    'is_expired' => $card->is_expired,
                ];
            }
        });
        usort($creditCards, function($val1, $val2) {
            return $val2['id'] > $val1['id'];
        });
        
        return response()->json([
            'credit_cards' => $creditCards,
        ]);
    }

    /**
     * @param Patient $patient
     *
     * @return JsonResponse
     */
    public function getCatalogItems(Patient $patient)
    {
        $items = [];

        if ($patient->is_self_pay) {
            $items = SquareCatalogItem::getCashItems();
        } else {
            $items = SquareCatalogItem::getInsuranceItems();
        }

        return response()->json([
            'catalog_items' => $items
        ]);
    }

    /**
     * @param Patient $patient
     *
     * @return JsonResponse
     */
    public function getChargeableAppointments(Patient $patient)
    {
        $copayStatuses = Status::getCompletedVisitCreatedStatusesId();
        $cancellationFeeStatuses = Status::getStatusesForCancellationFee();

        $appointments = Appointment::query()
            ->select([
                'id',
                'appointment_statuses_id',
                'time',
            ])
            ->with([
                'status',
                'lateCancellationTransaction:id,appointment_id,payment_amount',
                'officeallyTransaction:id,appointment_id,payment_amount',
            ])
            ->where('patients_id', $patient->id)
            ->whereDoesntHave('squareTransaction')
            ->whereIn('appointment_statuses_id', array_merge($copayStatuses, $cancellationFeeStatuses))
            ->orderBy('time', 'desc')
            ->get();

        $appointmentsForCopayDataset = [];
        $appointmentsForCancellationFeeDataset = [];

        foreach ($appointments as $appointment) {
            $date = Carbon::createFromTimestamp($appointment->time);
            $data = [
                'id' => $appointment->id,
                'text' => sprintf('%s (%s)', $date->format('m/d/Y'), optional($appointment->status)->status),
                'date' => $date->format('m/d/Y'),
                'time' => $date->format('h:i A'),
                'cancellation_fee' => optional($appointment->lateCancellationTransaction)->payment_amount / 100,
                'copay' => optional($appointment->officeallyTransaction)->payment_amount / 100,
            ];

            if (in_array($appointment->appointment_statuses_id, $copayStatuses)) {
                $appointmentsForCopayDataset[] = $data;
            } else if (in_array($appointment->appointment_statuses_id, $cancellationFeeStatuses)) {
                $appointmentsForCancellationFeeDataset[] = $data;
            }
        }

        return response()->json([
            'for_copay' => $appointmentsForCopayDataset,
            'for_cancellation_fee' => $appointmentsForCancellationFeeDataset,
        ]);
    }
    
    /**
     * @param Charge  $request
     * @param Patient $patient
     *
     * @return JsonResponse
     * @throws PaymentNotAddedException
     */
    public function charge(
        Charge $request,
        Patient $patient
    ) {
        try {
            $squareCustomerService = new Customer();
            $zip = $request->input('zip');
            $squareAccount = $squareCustomerService->createIfNotExist($patient, [
                'first_name' => $patient->first_name,
                'last_name' => $patient->last_name,
                'zip' => $zip
            ]);
            $payload = $request->all();
            if ($request->get('store_credit_card') && $request->get('card_nonce')) {
                $squareCustomerCardService = new CustomerCard();
                $card = $squareCustomerCardService->create($squareAccount, $request->get('card_nonce'), $zip);
                $payload['card_id'] = $card->getKey();
            }
            $payload['user_id'] = auth()->id();
            
            $paymentRepository = new CreditCardPaymentRepository(SquarePaymentMethod::where('slug', 'credit_card')->first(), $patient, new Appointment(), $payload);
            $paymentRepository->pay();
        } catch (SquareException $e) {
            \App\Helpers\SentryLogger::captureException($e);
            return response()->json([
                'errors' => $e->getErrors(),
            ], 400);
        }
        
        return response()->json(null, 204);
    }
}