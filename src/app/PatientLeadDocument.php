<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Scopes\PatientDocuments\DocumentsForAllScope;

class PatientLeadDocument extends Model {
    use SoftDeletes;

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new DocumentsForAllScope());
    }

    protected $table = 'patient_lead_documents';

    protected $guarded = [];

    protected $dates = ['deleted_at', 'created_at', 'updated_at'];

    public function documentType()
    {
        return $this->belongsTo('App\PatientDocumentType', 'document_type_id');
    }

    public function uploadInfo()
    {
        return $this->hasOne(PatientLeadDocumentUploadInfo::class, 'patient_lead_document_id');
    }

    public static function withoutAdminScope()
    {
        return static::withoutGlobalScope(DocumentsForAllScope::class);
    }
}
