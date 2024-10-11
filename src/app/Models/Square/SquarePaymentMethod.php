<?php

namespace App\Models\Square;

use App\Models\AppointmentPayment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Square\SquarePaymentMethod
 *
 * @property int                                                                            $id
 * @property string                                                                         $slug
 * @property string                                                                         $title
 * @property int                                                                            $order
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Square\SquarePaymentMethod whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Square\SquarePaymentMethod whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Square\SquarePaymentMethod whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Square\SquarePaymentMethod whereTitle($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\AppointmentPayment[] $appointmentPayments
 */
class SquarePaymentMethod extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'slug',
        'title',
        'order',
    ];
    
    protected $casts = [
        'order' => 'int',
    ];
    
    /**
     * @return HasMany
     */
    public function appointmentPayments()
    {
        return $this->hasMany(AppointmentPayment::class, 'payment_method_id');
    }
}
