<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\UserMeta
 *
 * @property int $user_id
 * @property string|null $firstname
 * @property string|null $lastname
 * @property string|null $about
 * @property string|null $photo
 * @property string|null $signature
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property int $has_access_rights_to_reassign_page
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PatientDocumentComment[] $documentComment
 * @property-read \App\User $user
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\UserMeta onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserMeta whereAbout($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserMeta whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserMeta whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserMeta whereFirstname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserMeta whereHasAccessRightsToReassignPage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserMeta whereLastname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserMeta wherePhoto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserMeta whereSignature($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserMeta whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserMeta whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserMeta withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\UserMeta withoutTrashed()
 * @mixin \Eloquent
 */
class UserMeta extends Model {

    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $table = 'users_meta';

    protected $primaryKey = 'user_id';

    protected $guarded = [];

    protected $dates = ['deleted_at'];

    /**
     * Returns user full name (Firstname + Lastname)
     * @return null | string
     */
    public function getFullname() {
        if(empty($this->firstname) && empty($this->lastname)) {
            return null;
        }
        return $this->firstname . " " . $this->lastname;
    }

    public function user() {
        return $this->belongsTo('\App\User');
    }

    public function documentComment()
    {
        return $this->hasMany(PatientDocumentComment::class,'admin_id','user_id');
    }
}
