<?php

namespace App\Models\Officeally;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Officeally\OfficeAllyCookie
 *
 * @property int $id
 * @property string $account_name
 * @property array|null $cookies
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Officeally\OfficeAllyCookie whereAccountName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Officeally\OfficeAllyCookie whereCookies($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Officeally\OfficeAllyCookie whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Officeally\OfficeAllyCookie whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Officeally\OfficeAllyCookie whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class OfficeAllyCookie extends Model
{
    protected $table = 'officeally_cookies';
    
    protected $fillable = [
        'account_name',
        'cookies',
    ];
    
    public function setCookiesAttribute($value)
    {
        if(is_array($value)) {
            $value = json_encode($value);
        }
        
        $this->attributes['cookies'] = encrypt($value);
    }
    
    public function getCookiesAttribute($value)
    {
        try {
            $value = decrypt($value);  // info: decrypt method will cause an error if cookies are empty
    
            return json_decode($value, true);
        } catch(DecryptException $e) {
            \App\Helpers\SentryLogger::officeAllyCaptureException($e);
            return null;
        }
        
    }
}
