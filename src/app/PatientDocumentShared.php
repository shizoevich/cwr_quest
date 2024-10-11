<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\PatientDocumentShared
 *
 * @property int $id
 * @property int $patient_documents_id
 * @property string $document_model
 * @property int $shared_document_methods_id
 * @property string $recipient
 * @property int|null $provider_id
 * @property int|null $admin_id
 * @property string $shared_link
 * @property string|null $external_id
 * @property string|null $shared_link_password
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \App\User|null $admin
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PatientDocumentDownloadInfo[] $documentDownloadInfo
 * @property-read \App\PatientDocumentSharedLog $documentSharedLog
 * @property-read \App\Provider|null $provider
 * @property-read \App\SharedDocumentMethod $sharedMethod
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentShared onlyEmailMethod()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentShared onlyFaxMethod()
 * @method static \Illuminate\Database\Query\Builder|\App\PatientDocumentShared onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentShared whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentShared whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentShared whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentShared whereDocumentModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentShared whereExternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentShared whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentShared wherePatientDocumentsId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentShared whereProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentShared whereRecipient($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentShared whereSharedDocumentMethodsId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentShared whereSharedLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentShared whereSharedLinkPassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentShared whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\PatientDocumentShared withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\PatientDocumentShared withoutTrashed()
 * @mixin \Eloquent
 */
class PatientDocumentShared extends Model
{
    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    protected $table = 'patient_document_shared';

    public function documentSharedLog() {
        return $this->hasOne(PatientDocumentSharedLog::class, 'patient_document_shared_id');
    }

    public function documentDownloadInfo() {
        return $this->hasMany(PatientDocumentDownloadInfo::class, 'patient_document_shared_id');
    }

    public function sharedMethod() {
        return $this->belongsTo(SharedDocumentMethod::class, 'shared_document_methods_id');
    }

    public function provider() {
        return $this->belongsTo(Provider::class, 'provider_id');
    }

    public function admin() {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function scopeOnlyEmailMethod($query) {
        $method = SharedDocumentMethod::getEmailMethod();
        return $query->where('shared_document_methods_id', $method);
    }

    public function scopeOnlyFaxMethod($query) {
        $method = SharedDocumentMethod::getFaxMethod();
        return $query->where('shared_document_methods_id', $method);
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
        return [
            'id' => $this->id,
            'patient_documents_id' => $this->patient_documents_id,
            'document_model' => $this->document_model,
            'shared_document_methods_id' => $this->shared_document_methods_id,
            'shared_document_methods_name' => optional($this->sharedMethod)->method,
            'recipient' => $this->recipient,
            'provider_id' => $this->provider_id,
            'provider_name' => optional($this->provider)->provider_name,
            'admin_id' => $this->admin_id,
            'shared_link' => $this->shared_link,
            'external_id' => $this->external_id,
            'shared_link_password' => $this->shared_link_password,
        ];
    }

    public function getCreateLogMessage()
    {
        return 'PatientDocumentShared created: ' . $this->getLogMessageIdentifier();
    }

    public function getUpdateLogMessage($dirtyFields = null)
    {
        if (empty($dirtyFields)) {
            $dirtyFields = $this->getDirtyWithOriginal();
        }

        $messagesList = [];

        foreach ($this->getScalarChangeableFields() as $fieldName => $message) {
            if (isset($dirtyFields[$fieldName])) {
                $messagesList[] = "$message changed from '{$dirtyFields[$fieldName]['prev']}' to '{$dirtyFields[$fieldName]['curr']}'";
            }
        }

        return 'PatientDocumentShared updated: ' . implode('; ', $messagesList);
    }

    public function getDeleteLogMessage()
    {
        return 'PatientDocumentShared deleted: ' . $this->getLogMessageIdentifier();
    }

    public function getRestoreLogMessage()
    {
        return 'PatientDocumentShared restored: ' . $this->getLogMessageIdentifier();
    }

    public function getLogMessageIdentifier()
    {
        return "{$this->id}; {$this->patient_documents_id}; '{$this->shared_link};'"
            . Carbon::parse($this->created_at)->toDateString();;
    }

    public function getScalarChangeableFields()
    {
        return [
            'patient_documents_id' => 'Patient document id',
            'document_model' => 'Document model',
            'shared_document_methods_id' => 'Shared document methods id',
            'recipient' => 'Recipient',
            'provider_id' => 'Provider id',
            'admin_id' => 'Admin id',
            'shared_link' => 'Shared link',
            'external_id' => 'External id',
            'shared_link_password' => 'Shared link password',
        ];
    }
}
