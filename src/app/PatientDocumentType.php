<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * @todo refactor this class (add slug and change methods for retrieving document type id, add document type caching)
 * App\PatientDocumentType
 *
 * @property int $id
 * @property string $type
 * @property int $parent
 * @property int $clickable
 * @property int $ind
 * @property int $only_for_admin
 * @property-read \App\PatientDocumentTypeDefaultAddresses $defaultAddress
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentType whereClickable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentType whereInd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentType whereOnlyForAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentType whereParent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientDocumentType whereType($value)
 * @mixin \Eloquent
 */
class PatientDocumentType extends Model
{
    protected $table = 'patient_document_types';

    const INITIAL_ASSESSMENT_TYPE = 1;

    const REQUEST_FOR_REAUTHORIZATION_TYPE = 2;

    const DISCHARGE_SUMMARY_TYPE = 3;

    const NEW_PATIENT_DOCUMENT_NAME = 'Patient Information, Informed Consent, Privacy Notice';

    const PAYMENT_FOR_SERVICE_DOCUMENT_NAME = 'Payment for Service and Fee Arrangements';

    public $timestamps = false;

    protected $guarded = [];

    public static function getTree() {
        $dataset = static::query()
            ->whereNull('deleted_at')
            ->orderBy('ind', 'asc');
        if(!Auth::user()->isAdmin()) {
            $dataset = $dataset->where('only_for_admin', false);
        }
        $dataset = $dataset->get()->toArray();
        $mappedDataset = [];
        foreach($dataset as $item) {
            $item['ind'] = intval($item['ind']);
            $item['parent'] = intval($item['parent']);
            $item['clickable'] = boolval($item['clickable']);
            $mappedDataset[$item['id']] = $item;
        }

        $tree = [];

        foreach ($mappedDataset as $id => &$node) {
            $uid = uniqid();
            if ($node['parent'] === 0){
                $tree[$uid] = &$node;
            } else {
                $mappedDataset[$node['parent']]['childs'][$uid] = &$node;
            }
        }
        return $tree;
    }

    /**
     * Returns "New patient..." document type ID
     * @return mixed
     */
    public static function getNewPatientId() {
        return static::where('type', 'Patient Information / Informed Consent / Privacy Notice')
            ->select('id')->first()['id'];
    }
    
    /**
     * @return mixed
     */
    public static function getPaymentForServiceId() {
        return static::where('type', 'Payment for Service and Fee Arrangements')
            ->select('id')->first()['id'];
    }
    
    /**
     * @return mixed
     */
    public static function getTelehealthId() {
        return static::where('type', 'Telehealth Consent Form')
                   ->select('id')->first()['id'];
    }
    
    /**
     * @return mixed
     */
    public static function getAgreementForServiceId() {
        return static::where('type', 'Agreement for Service & HIPAA Privacy Notice & Patient Rights & Notice to Psychotherapy Clients')
                   ->select('id')->first()['id'];
    }

    /**
     * Returns "Authorization to Release Confidential Information" document type ID
     * @return mixed
     */
    public static function getAuthToReleaseId() {
        return static::where('type', 'Authorization to Release Confidential Information')
            ->select('id')->first()['id'];
    }

    /**
     * Returns "Image / Picture" document type ID
     * @return mixed
     */
    public static function getImageId() {
        return static::where('type', 'Image / Picture')
            ->select('id')->first()['id'];
    }
    
    public static function getSupportingDocumentId() {
        return static::where('type', 'Supporting Document')
                   ->select('id')->first()['id'];
    }
    
    public static function getInsuranceSupportingDocumentId() {
        return static::where('type', 'Insurance')
            ->where('parent', '=', self::getSupportingDocumentId())
            ->select('id')->first()['id'];
    }
    
    public static function getDriversLicenseSupportingDocumentId() {
        return static::where('type', 'Driver\'s License')
                   ->where('parent', '=', self::getSupportingDocumentId())
                   ->select('id')->first()['id'];
    }

    public static function getFaxId() {
        return static::where('type', 'Fax')
            ->select('id')->first()['id'];
    }

    public static function getEligibilityVerificationId() {
        return static::select('id')
            ->where('type', 'Eligibility Verification')->first()['id'];
    }

    public static function getFileTypeIDsLikeDischarge() {

        $ids = Cache::rememberForever('file_type_ids_like_discharge', function() {
            return static::select('id')
                ->where('type_id', '=', self::DISCHARGE_SUMMARY_TYPE)
                ->get()
                ->pluck('id')
                ->toArray();
        });

        return $ids;
    }

    public static function getFileTypeIDsLikeInitialAssessment() {

        $ids = Cache::rememberForever('file_type_ids_like_initial_assessment', function() {
            return static::select('id')
                ->where('type_id', '=', self::INITIAL_ASSESSMENT_TYPE)
                ->where('clickable', '=', true)
                ->get()
                ->pluck('id')
                ->toArray();
        });

        return $ids;
    }

    public static function getFileTypeIDsLikeReauthorization() {
        $ids = Cache::rememberForever('file_type_ids_like_reauthorization', function() {
            return static::select('id')
                ->where('type_id', '=', self::REQUEST_FOR_REAUTHORIZATION_TYPE)
                ->where('clickable', 1)
                ->get()
                ->pluck('id')
                ->toArray();
        });

        return $ids;
    }

    public function defaultAddress() {
        return $this->hasOne(PatientDocumentTypeDefaultAddresses::class, 'patient_document_types_id','id');
    }
}
