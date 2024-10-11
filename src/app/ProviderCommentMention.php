<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\ProviderCommentMention
 *
 * @property int $provider_id
 * @property int $comment_id
 * @property string $model
 * @property string|null $readed_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\PatientComment $comment
 * @property-read \App\Provider $provider
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderCommentMention whereCommentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderCommentMention whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderCommentMention whereModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderCommentMention whereProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderCommentMention whereReadedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderCommentMention whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ProviderCommentMention extends Model
{
    protected $table = 'provider_comment_mentions';

    public $incrementing = false;

    protected $guarded = ['readed_at', 'admin_readed_at'];

    public function provider() {
        return $this->belongsTo(Provider::class, 'provider_id', 'id');
    }

    public function comment() {
        return $this->belongsTo(PatientComment::class, 'comment_id', 'id');
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeReaded($query)
    {
        return $query->whereNotNull('readed_at');
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeAdminReaded($query)
    {
        return $query->whereNotNull('admin_readed_at');
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeNotReaded($query)
    {
        return $query->whereNull('readed_at');
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeAdminNotReaded($query)
    {
        return $query->whereNull('admin_readed_at');
    }
}
