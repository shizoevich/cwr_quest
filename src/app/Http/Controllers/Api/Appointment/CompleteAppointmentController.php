<?php

namespace App\Http\Controllers\Api\Appointment;

use App\Appointment;
use App\Exceptions\Officeally\Appointment\PaymentNotAddedException;
use App\Exceptions\Square\SquareException;
use App\Http\Requests\Appointments\CompleteAppointment\Pay;
use App\Http\Requests\Appointments\CompleteAppointment\Show;
use App\Models\Square\SquarePaymentMethod;
use App\Patient;
use App\Http\Controllers\Controller;
use App\Repositories\Appointment\Payment\AbstractPaymentRepository;
use Illuminate\Http\JsonResponse;

/**
 * Class CompleteAppointmentController
 * @package App\Http\Controllers\Api\Appointment
 */
class CompleteAppointmentController extends Controller
{
    /**
     * @param Show        $request
     * @param Patient     $patient
     * @param Appointment $appointment
     *
     * @return JsonResponse
     */
    public function show(Show $request, Patient $patient, Appointment $appointment)
    {
        $appointment->load(['paymentInfo']);
        $patient->load([
            'insurancePlan',
            'diagnoses'
        ]);
        
        return response()->json([
            'appointment'     => $appointment,
            'patient'         => $patient,
            'payment_methods' => SquarePaymentMethod::query()->orderBy('order')->get()
        ]);
    }
    
    /**
     * @param Pay                       $request
     * @param Patient                   $patient
     * @param Appointment               $appointment
     * @param AbstractPaymentRepository $paymentRepository
     *
     * @return JsonResponse
     * @throws PaymentNotAddedException
     */
    public function pay(
        Pay $request,
        Patient $patient,
        Appointment $appointment,
        AbstractPaymentRepository $paymentRepository
    ) {
        $paymentPayload = [
            'payment_method_id'                 => $request->input('payment_method_id'),
            'amount'                            => $request->input('amount'),
            'is_square_transaction_success'     => false,
            'is_officeally_transaction_success' => false,
        ];
        $additionalData = [];
        $additionalDataKeys = [
            'check_no',
            'card_id',
            'card_nonce',
            'email',
        ];
        foreach ($additionalDataKeys as $key) {
            if($request->has($key)) {
                $additionalData[$key] = $request->input($key);
            }
            
        }
        if(!empty($additionalData)) {
            $paymentPayload['additional_data'] = $additionalData;
        }
    
        try {
            $paymentRepository->pay();
            $paymentPayload['is_square_transaction_success'] = true;
        } catch (SquareException $e) {
            \App\Helpers\SentryLogger::captureException($e);
            return response()->json([
                'errors' => $e->getErrors(),
            ], 400);
        } finally {
            $paymentInfoModel = $appointment->paymentInfo()->create($paymentPayload);
        }
        $paymentRepository->addPaymentToOfficeAlly();
        $paymentInfoModel->update(['is_officeally_transaction_success' => true]);
        
        return response()->json(null, 204);
    }
}
