<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\PatientDocumentTypeDefaultAddresses
 *
 * @property int $id
 * @property int $patient_document_types_id
 * @property string $email
 * @property string|null $fax
 * @property string|null $password
 * @property-read \App\PatientDocumentType $documentType
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentTypeDefaultAddresses whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentTypeDefaultAddresses whereFax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentTypeDefaultAddresses whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentTypeDefaultAddresses wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentTypeDefaultAddresses wherePatientDocumentTypesId($value)
 * @mixin \Eloquent
 */
class PatientDocumentTypeDefaultAddresses extends Model
{
    protected $table = 'patient_document_type_default_addresses';

    protected $fillable = [
      'email',
      'fax'
    ];

    public $timestamps = false;

    public function documentType()
    {
        return $this->belongsTo(PatientDocumentType::class, 'patient_document_types_id','id');
    }
}
