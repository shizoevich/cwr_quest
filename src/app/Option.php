<?php

namespace App;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Option
 *
 * @property int $id
 * @property string $option_name
 * @property string $option_value
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Option whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Option whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Option whereOptionName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Option whereOptionValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Option whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Option extends Model {
    protected $table = 'options';
    
    const OA_ACCOUNT_1 = 'groupbwt';
    const OA_ACCOUNT_2 = 'xyz_test';
    const OA_ACCOUNT_3 = 'ericgrigs';

    protected $guarded = ['id'];

    /**
     * @param $optionName
     * Get option value by option name
     * @return mixed
     */
    public static function getOptionValue($optionName) {
        return self::getOption($optionName)['option_value'];
    }

    /**
     * @param $optionName
     * Get option model by option name
     * @return mixed
     */
    public static function getOption($optionName) {
        return Option::where('option_name', $optionName)->first();
    }

    /**
     * @param $optionName
     * @param $optionValue
     * Add new or update existing option
     * @return mixed
     */
    public static function setOptionValue($optionName, $optionValue) {
        return Option::updateOrCreate(['option_name' => $optionName],[
            'option_name' => $optionName,
            'option_value' => $optionValue,
        ]);
    }
    
    public static function getParserConfig(string $accountName)
    {   
        // use dev_officeally_credentials for testing
        $credentials = self::getOptionValue('officeally_credentials');
        // $credentials = self::getOptionValue('dev_officeally_credentials');
        // ---

        $credentials = json_decode($credentials, true);
        $config = config('parser');
        $config['login'] = data_get($credentials, "{$accountName}.login");
        try {
            $config['password'] = decrypt(data_get($credentials, "{$accountName}.password"));
        } catch(DecryptException $e) {
            $config['password'] = '';
            \App\Helpers\SentryLogger::captureException($e);
        }
        
        return $config;
    }
}
