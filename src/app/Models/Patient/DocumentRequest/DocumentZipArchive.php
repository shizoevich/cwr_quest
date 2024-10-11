<?php

namespace App\Models\Patient\DocumentRequest;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class DocumentZipArchive
 *
 * @package App\Models\Patient\DocumentRequest
 *
 * @property int $id
 * @property int $patient_id
 * @property int $user_id
 * @property string $zip_file_unique_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class DocumentZipArchive extends Model
{
    use SoftDeletes; 

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'patient_id',
        'user_id',
        'zip_file_unique_name',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'patient_id' => 'int',
        'user_id' => 'int',
        'zip_file_unique_name' => 'string',
    ];
}
