<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\SharedDocumentMethod
 *
 * @property int $id
 * @property string $method
 * @property-read \App\PatientDocumentShared $log
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SharedDocumentMethod whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SharedDocumentMethod whereMethod($value)
 * @mixin \Eloquent
 */
class SharedDocumentMethod extends Model
{
    protected $table = 'shared_document_methods';

    public function log() {
        return $this->hasOne(PatientDocumentShared::class, 'shared_document_methods_id');
    }

    public static function getEmailMethod() {
        return static::where('method', '=', 'email')
            ->first()['id'];
    }

    public static function getFaxMethod() {
        return static::where('method', '=', 'fax')
            ->first()['id'];
    }
}
