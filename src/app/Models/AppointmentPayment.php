<?php

namespace App\Models;

use App\Appointment;
use App\Models\Square\SquarePaymentMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\AppointmentPayment
 *
 * @property int                 $id
 * @property int                 $appointment_id
 * @property int                 $payment_method_id
 * @property int                 $amount Amount in cents
 * @property bool                $is_square_transaction_success
 * @property bool                $is_officeally_transaction_success
 * @property array|null          $additional_data
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AppointmentPayment whereAdditionalData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AppointmentPayment whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AppointmentPayment whereAppointmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AppointmentPayment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AppointmentPayment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AppointmentPayment whereIsOfficeallyTransactionSuccess($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AppointmentPayment whereIsSquareTransactionSuccess($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AppointmentPayment wherePaymentMethodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AppointmentPayment whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \App\Appointment $appointment
 * @property-read \App\Models\Square\SquarePaymentMethod $paymentMethod
 */
class AppointmentPayment extends Model
{
    protected $fillable = [
        'appointment_id',
        'payment_method_id',
        'amount',
        'is_square_transaction_success',
        'is_officeally_transaction_success',
        'additional_data',
    ];
    
    protected $casts = [
        'appointment_id'                    => 'int',
        'payment_method_id'                 => 'int',
        'amount'                            => 'int',
        'is_square_transaction_success'     => 'bool',
        'is_officeally_transaction_success' => 'bool',
        'additional_data'                   => 'array',
    ];
    
    /**
     * @return BelongsTo
     */
    public function paymentMethod()
    {
        return $this->belongsTo(SquarePaymentMethod::class, 'payment_method_id');
    }
    
    /**
     * @return BelongsTo
     */
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}
