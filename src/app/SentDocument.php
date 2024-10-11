<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\SentDocument
 *
 * @property int $id
 * @property int $user_id
 * @property int $document_id
 * @property string $document_model
 * @property bool $is_sent
 * @property \Carbon\Carbon|null $approved_at
 * @property string $authorization_no
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SentDocument whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SentDocument whereAuthorizationNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SentDocument whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SentDocument whereDocumentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SentDocument whereDocumentModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SentDocument whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SentDocument whereIsSent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SentDocument whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SentDocument whereUserId($value)
 * @mixin \Eloquent
 */
class SentDocument extends Model
{
    protected $table = 'sent_documents';

    protected $guarded = [];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'document_id' => 'integer',

        'document_model' => 'string',
        'authorization_no' => 'string',

        'is_sent' => 'boolean',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'approved_at',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
