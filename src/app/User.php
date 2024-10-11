<?php

namespace App;

use App\Models\AuthLog;
use App\Models\Patient\Comment\PatientCommentMention;
use App\Models\UpdateNotification;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * App\User
 *
 * @property int $id
 * @property int|null $provider_id
 * @property string $email
 * @property string $password
 * @property string|null $remember_token
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property int|null $login_at
 * @property \Carbon\Carbon|null $password_updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PatientAssessmentForm[] $assessmentForms
 * @property-read \App\UserMeta $meta
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read \App\Provider|null $provider
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Role[] $roles
 * @property-read \App\TherapistSurvey $therapistSurvey
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PatientDocumentUploadInfo[] $uploadedDocuments
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\User onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereLoginAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User wherePasswordUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\User withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\User withoutTrashed()
 * @mixin \Eloquent
 * @property-read \App\UserMeta $signature
 */
class User extends Authenticatable
{
    use Notifiable;

    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $dates = ['deleted_at', 'password_updated_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'provider_id',
        'email',
        'password',
        'user_id',
        'password_updated_at',
        'google_id',
        'profile_completed_at',
        'signature_token'
    ];

    protected static function boot() {
        parent::boot();

        static::deleting(function($user) {
            $user->meta()->delete();
            if($user->isProviderAttached()) {
                $user->provider()->delete();
            }
        });
    }

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * User -> UserMeta relationship
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function meta() {
        return $this->hasOne('\App\UserMeta');
    }

    /**
     * User -> Provider relationship
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function provider() {
        return $this->belongsTo('\App\Provider');
    }

    public function roles() {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id');
    }

    public function therapistSurvey()
    {
        return $this->hasOne(TherapistSurvey::class, 'user_id','id');
    }

    public function signature() {
        return $this->hasOne(UserMeta::class, 'user_id', 'id');
    }

    public function uploadedDocuments() {
        return $this->hasMany(PatientDocumentUploadInfo::class)->where('document_model', '!=', 'App\PatientNote');
    }

    public function assessmentForms() {
        return $this->hasMany(PatientAssessmentForm::class, 'creator_id', 'id');
    }

    public function updateNotifications()
    {
        return $this->belongsToMany(UpdateNotification::class, 'update_notification_user');
    }

    public function mentions(): HasMany
    {
        return $this->hasMany(PatientCommentMention::class, 'user_id', 'id');
    }

    /**
     * Returns true if user is admin or secretary
     * @return bool
     */
    public function isAdmin() {
        return intval($this->hasRole('admin') || $this->hasRole('secretary') || $this->hasRole('patient_relation_manager'));
    }

    /**
     * Returns true if user is admin
     * @return bool
     */
    public function isOnlyAdmin() {
        return intval($this->hasRole('admin'));
    }

    /**
     * Returns true if user is admin
     * @return bool
     */
    public function isProvider() {
        return intval($this->hasRole('provider'));
    }
    
    /**
     * Returns true if user is admin
     * @return bool
     */
    public function isInsuranceAudit() {
        return intval($this->hasRole('insurance_audit'));
    }

    /**
     * Returns true if user is secretary
     * @return int
     */
    public function isSecretary() {
        return intval($this->hasRole('secretary') && !$this->hasRole('admin'));
    }

    public function isPatientRelationManager() {
        return intval($this->hasRole('patient_relation_manager') && !$this->hasRole('admin'));
    }

    public function isSupervisor() {
        return $this->isProvider() && $this->isProviderAttached() ? $this->provider->is_supervisor : false;
    }

    public function hasRole($roleName) {
        $roleName = strtolower(trim($roleName));
        return (int)$this->roles->contains('role', $roleName);
    }

    public function hasRoles(array $roles, $returnArray = false) {
        $response = [];
        foreach($roles as $role) {
            $response[$role] = $this->hasRole($role);
            if(!$returnArray && !$response[$role]) {
                return 0;
            }
        }
        if(!$returnArray) {
            return 1;
        }

        return $response;
    }

    /**
     * Returns user full name (Firstname + Lastname)
     * @return null | string
     */
    public function getFullname()
    {
        if ($this->isOnlyProvider()) {
            return $this->provider->provider_name;
        }
        
        if ($this->meta === null) {
            return null;
        }
        return $this->meta->getFullname();
    }

    /**
     * Returns therapist full name
     * @return null | string
     */
    public function getTherapistFullname()
    {
        if ($this->therapistSurvey === null) {
            return null;
        }
        return $this->therapistSurvey->getFullname();
    }

    /**
     * Returns user full name depending on his role
     * @return null | string
     */
    public function getGeneralFullname()
    {
        return $this->isOnlyProvider() ? $this->getTherapistFullname() : $this->getFullname();
    }

    /**
     * Returns provider name
     * @return null | string
     */
    public function getProviderName()
    {
        if ($this->isProviderAttached()) {
            return $this->provider->provider_name;
        }

        return null;
    }

    public function getNameAttribute()
    {
        return $this->getFullname() ?? $this->getProviderName();
    }

    /**
     * Returns true if user has relationship with Provider
     * @return bool
     */
    public function isProviderAttached()
    {
        return !empty($this->provider_id);
    }

    /**
     * @return bool
     */
    public function isOnlyProvider(): bool
    {
        return !$this->isAdmin() && $this->isProviderAttached();
    }

    public static function getNewUsersCount() {
        if(Schema::hasTable('users') && Schema::hasTable('users_meta')
            && Schema::hasTable('roles')) {
            $roles[] = Role::getRoleId('admin');
            $roles[] = Role::getRoleId('secretary');

            $count = User::withTrashed()->join('users_meta', 'users_meta.user_id', '=', 'users.id')
                ->whereDoesntHave('roles', function($query) use (&$roles) {
                    $query->whereIn('role_id', $roles);
                })->whereNull('users.provider_id')
                ->count();

            return $count;
        }

        return 0;
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function authLog()
    {
        return $this->hasMany(AuthLog::class);
    }

    public function checkAllowedRoles($allowedRoleIds)
    {
        return $this->roles->pluck('id')->every(function ($value) use(&$allowedRoleIds) {
            return in_array($value, $allowedRoleIds);
        });
    }

    public static function getUsersWithAccessToAllSidebarMessages(): array
    {
        return Cache::rememberForever('users-with-access-to-all-sidebar-messages-ids', function () {
            return self::query()
                ->where(function ($query) {
                    $query->whereHas('roles', function ($query) {
                        $query->where('role', '=', 'secretary');
                    })->whereDoesntHave('roles', function ($query) {
                        $query->where('role', '=', 'admin');
                    });
                })
                ->orWhereHas('meta', function ($query) {
                    $query->where('has_access_to_all_sidebar_messages', 1);
                })
                ->pluck('id')
                ->toArray();
        });
    }

    public function canSeeAllSidebarMessages(): bool
    {
        return $this->isSecretary() || optional($this->meta)->has_access_to_all_sidebar_messages;
    }
}
