<?php

namespace App\Models\Square;

use App\Appointment;
use App\Models\Patient\PatientTransaction;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Square\SquareTransaction
 *
 * @property int $id
 * @property string $external_id
 * @property int $location_id
 * @property int $customer_id
 * @property int $transaction_type_id
 * @property int $amount_money
 * @property int|null $card_brand_id
 * @property int $card_last_four
 * @property int|null $entry_method_id
 * @property \Carbon\Carbon|null $transaction_date
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $processed_at
 * @property-read \App\Models\Square\SquareTransactionEntryMethod|null $entryMethod
 * @property-read \App\Models\Square\SquareLocation $location
 * @property-read \App\Models\Patient\PatientTransaction $patientTransaction
 * @property-read \App\Models\Square\SquareTransactionType $transactionType
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Square\SquareTransaction whereAmountMoney($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Square\SquareTransaction whereCardBrandId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Square\SquareTransaction whereCardLastFour($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Square\SquareTransaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Square\SquareTransaction whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Square\SquareTransaction whereEntryMethodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Square\SquareTransaction whereExternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Square\SquareTransaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Square\SquareTransaction whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Square\SquareTransaction whereProcessedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Square\SquareTransaction whereTransactionDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Square\SquareTransaction whereTransactionTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Square\SquareTransaction whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property \Carbon\Carbon|null $preprocessed_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Square\SquareTransaction wherePreprocessedAt($value)
 */
class SquareTransaction extends Model
{
    protected $table = 'square_transactions';

    protected $guarded = [];

    protected $dates = [
        'transaction_date',
        'created_at',
        'updated_at',
        'processed_at',
        'preprocessed_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'location_id' => 'integer',
        'customer_id' => 'integer',
        'transaction_type_id' => 'integer',
        'amount_money' => 'integer',
        'card_last_four' => 'integer',
        'external_id' => 'string',
        'card_brand' => 'string',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function location() {
        return $this->belongsTo(SquareLocation::class, 'location_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function entryMethod() {
        return $this->belongsTo(SquareTransactionEntryMethod::class, 'entry_method_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function transactionType() {
        return $this->belongsTo(SquareTransactionType::class, 'transaction_type_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function patientTransaction() {
        return $this->morphOne(PatientTransaction::class, 'transactionable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'appointment_id', 'id');
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeUnPreprocessed($query)
    {
        return $query->whereNull('preprocessed_at');
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopePreprocessed($query)
    {
        return $query->whereNotNull('preprocessed_at');
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeUnProcessed($query)
    {
        return $query->whereNull('processed_at');
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeProcessed($query)
    {
        return $query->whereNotNull('processed_at');
    }

    public function getDirtyWithOriginal()
    {
        $result = [];
        $dirtyFields = $this->getDirty();

        foreach ($dirtyFields as $fieldName => $fieldValue) {
            $result[$fieldName] = [
                'prev' => $this->getOriginal($fieldName),
                'curr' => $fieldValue,
            ];
        }

        return $result;
    }

    public function getLogData()
    {
        try {
            return [
                'id' => $this->id,
                'external_id' => $this->external_id,
                'location_id' => $this->location_id,
                'customer_id' => $this->customer_id,
                'transaction_type_id' => $this->transaction_type_id,
                'amount_money' => $this->amount_money,
                'card_brand_id' => $this->card_brand_id,
                'card_last_four' => $this->card_last_four,
                'entry_method_id' => $this->entry_method_id,
                'order_id' => $this->order_id,
                'transaction_date' => $this->transaction_date,
                'processed_at' => $this->processed_at,
                'preprocessed_at' => $this->preprocessed_at,
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getCreateLogMessage()
    {
        try {
            return 'Square transaction created: ' . $this->getLogMessageIdentifier();
        } catch (\Exception $e) {
            return 'Square transaction created';
        }
    }

    public function getUpdateLogMessage($dirtyFields = null)
    {
        try {
            if (empty($dirtyFields)) {
                $dirtyFields = $this->getDirtyWithOriginal();
            }

            $messagesList = [];

            foreach ($this->getScalarChangeableFields() as $fieldName => $message) {
                if (isset($dirtyFields[$fieldName])) {
                    $messagesList[] = "$message changed from '{$dirtyFields[$fieldName]['prev']}' to '{$dirtyFields[$fieldName]['curr']}'";
                }
            }

            return 'Square transaction updated: ' . implode('; ', $messagesList);
        } catch (\Exception $e) {
            return 'Square transaction updated';
        }
    }

    public function getDeleteLogMessage()
    {
        try {
            return 'Square transaction deleted: ' . $this->getLogMessageIdentifier();
        } catch (\Exception $e) {
            return 'Square transaction deleted';
        }
    }

    public function getLogMessageIdentifier()
    {
        return "{$this->id}; {$this->external_id}; "
            . Carbon::parse($this->created_at)->toDateTimeString();
    }

    public function getScalarChangeableFields()
    {
        return [
            'external_id' => 'External id',
            'location_id' => 'Location id',
            'customer_id' => 'Customer id',
            'transaction_type_id' => 'Transaction type id',
            'amount_money' => 'Amount money',
            'card_brand_id' => 'Card brand id',
            'card_last_four' => 'Card last four',
            'entry_method_id' => 'Entry method id',
            'order_id' => 'Order id',
            'transaction_date' => 'Transaction date',
            'processed_at' => 'Processed at',
            'preprocessed_at' => 'Preprocessed at',
        ];
    }
}
