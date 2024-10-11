<?php

namespace App;

use App\Models\Patient\Comment\PatientCommentMention;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * App\PatientDocumentComment
 *
 * @property int $id
 * @property int $patient_documents_id
 * @property string $document_model
 * @property int|null $provider_id
 * @property int|null $admin_id
 * @property string $content
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property bool $is_system_comment
 * @property-read \App\PatientDocument $document
 * @property-read \App\Provider|null $provider
 * @property-read \App\UserMeta|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentComment whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentComment whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentComment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentComment whereDocumentModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentComment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentComment whereIsSystemComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentComment wherePatientDocumentsId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentComment whereProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentComment whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string|null $unique_id
 * @property string|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentComment whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentComment whereUniqueId($value)
 */
class PatientDocumentComment extends Model
{
    protected $table = 'patient_document_comments';

    protected $casts = [
        'is_system_comment' => 'boolean'
    ];

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(UserMeta::class, 'admin_id','user_id');
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class, 'provider_id','id');
    }

    public function document()
    {
        return $this->belongsTo(PatientDocument::class, 'patient_documents_id','id');
    }

    public function mentions()
    {
        return $this->hasMany(PatientCommentMention::class, 'comment_id', 'id')->where('model', 'PatientDocumentComment');
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
            'provider_id' => $this->provider_id,
            'provider_name' => optional($this->provider)->provider_name,
            'admin_id' => $this->admin_id,
            'content' => $this->content,
            'unique_id' => $this->unique_id,
            'is_system_comment' => $this->is_system_comment,
        ];
    }

    public function getCreateLogMessage()
    {
        return 'PatientDocumentComment created: ' . $this->getLogMessageIdentifier();
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

        return 'PatientDocumentComment updated: ' . implode('; ', $messagesList);
    }

    public function getDeleteLogMessage()
    {
        return 'PatientDocumentComment deleted: ' . $this->getLogMessageIdentifier();
    }

    public function getRestoreLogMessage()
    {
        return 'PatientDocumentComment restored: ' . $this->getLogMessageIdentifier();
    }

    public function getLogMessageIdentifier()
    {
        return "{$this->id}; {$this->patient_documents_id}; {$this->document_model}; {$this->content};"
            . Carbon::parse($this->created_at)->toDateString();
    }

    public function getScalarChangeableFields()
    {
        return [
            'patient_documents_id' => 'Patient document id',
            'document_model' => 'Document model',
            'provider_id' => 'Provider id',
            'admin_id' => 'Admin id',
            'content' => 'Content',
            'unique_id' => 'Unique id',
            'is_system_comment' => 'Is system comment',
        ];
    }
}
