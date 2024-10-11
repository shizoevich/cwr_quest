<?php

use App\Helpers\Optional;
use App\KaiserAppointment;
use App\Models\Diagnose;

function create_email_by_first_last(string $firstName, string $lastName) {
    $email = str_slug($firstName . ' ' . $lastName, '.') . '@' . config('app.email-domain2');

    return $email;
}

function money_round($n)
{
    return ((floor($n) == round($n, 2)) ? number_format($n) : number_format($n, 2));
}

function format_money($amount, $symbol = '$', $decimals = 2)
{
    $formatted = '';
    if($amount < 0) {
        $formatted = '-';
    }
    $formatted .= $symbol . number_format(abs($amount), $decimals);
    
    return $formatted;
}

function camelToTitle($camelStr)
{
    $intermediate = preg_replace('/(?!^)([[:upper:]][[:lower:]]+)/',
                          ' $0',
                          $camelStr);
    $titleStr = preg_replace('/(?!^)([[:lower:]])([[:upper:]])/',
                          '$1 $2',
                          $intermediate);
    return $titleStr;
}

function mask_password_field(array $data)
{
    foreach ($data as $key => &$item) {
        if(is_array($item)) {
            $data[$key] = mask_password_field($item);
        } else if($key === 'password' || $key === 'password_confirmation') {
            $item = '********';
        }
    }

    return $data;
}

function sanitize_name($name)
{
    return $name ? title_case(str_slug($name, ' ')) : null;
}

if (!function_exists('__data_get')) {
    /**
     * @param $target
     * @param $key
     * @param null $default
     *
     * @return mixed|null
     */
    function __data_get($target, $key, $default = null)
    {
        $item = data_get($target, $key, $default);
        
        return empty($item) ? $default : $item;
    }
}

if (!function_exists('remove_nbsp')) {
    
    /**
     * @param $string
     *
     * @return string
     */
    function remove_nbsp($string)
    {
        return trim($string, chr(0xC2).chr(0xA0));
    }
}

if (!function_exists('seconds_to_human_readable_duration')) {
    
    /**
     * @param int $seconds
     */
    function seconds_to_human_readable_duration($seconds)
    {
        $minutes = (int)($seconds / 60 % 60);
        $hours = (int)($seconds / 3600);
        $days = (int)floor($hours / 24);
        $hours -= $days * 24;
        $formatted = '';
        if($days) {
            $formatted .= " {$days} day" . ($days > 1 ? 's' : '');
        }
        if($hours) {
            $formatted .= " {$hours} hour" . ($hours > 1 ? 's' : '');
        }
        if($minutes) {
            $formatted .= " {$minutes} minute" . ($minutes > 1 ? 's' : '');
        }
        if(!$days && !$hours && !$minutes) {
            $formatted = '0 minutes';
        }
        
        return trim($formatted);
    }
}

if (!function_exists('parse_diagnose')) {
    
    /**
     * @param       $diagnose
     * @param false $createIfNotExists
     * @param false $allowDiagnoseWithoutCode
     *
     * @return array|\Illuminate\Database\Eloquent\Model|null
     */
    function parse_diagnose($diagnose, $createIfNotExists = false, $allowDiagnoseWithoutCode = false)
    {
        $diagnose = trim($diagnose, ' "');
        $matches = [];
        preg_match('/^(?<code>[\w\.]+) - (?<description>.+)$/', $diagnose, $matches);
        if(isset($matches['code']) && isset($matches['description'])) {
            $matches['code'] = str_replace('.', '', $matches['code']);
            
            $diagnoseData = ['code' => $matches['code'], 'description' => $matches['description']];
            if($createIfNotExists) {
                return Diagnose::query()->firstOrCreate(['code' => $diagnoseData['code']], ['description' => $diagnoseData['description']]);
            }
            
            return $diagnoseData;
        } else if($allowDiagnoseWithoutCode && $createIfNotExists && $diagnose) {
            return Diagnose::query()->firstOrCreate(['code' => null, 'description' => $diagnose], ['is_custom' => 1]);
        }
        
        return null;
    }
}

if (!function_exists('prepare_mrn')) {
    function prepare_mrn($mrn)
    {
        if(strlen($mrn) === 0) {
            return '';
        }
        $mrn = str_pad($mrn, KaiserAppointment::MAX_MRN_LENGTH, '0', STR_PAD_LEFT);
    
        return $mrn;
    }
}

if (!function_exists('split_phone')) {
    function split_phone($phone)
    {
        if(!$phone) {
            return null;
        }
        $matches = [];
    
        preg_match('/^\((?<area_code>\d{3})\)-(?<prefix>\d{3})-(?<number>\d{4})$/', $phone, $matches);
    
        return [
            'area_code' => $matches['area_code'] ?? null,
            'prefix'    => $matches['prefix'] ?? null,
            'number'    => $matches['number'] ?? null
        ];
    }
}

if (!function_exists('sanitize_phone')) {
    function sanitize_phone($phone)
    {
        if(!$phone) {
            return null;
        }
        
        return preg_replace('/[^0-9]/', '', $phone);
    }
}

if (!function_exists('format_phone')) {
    function format_phone($phone)
    {
        if (! $phone) {
            return null;
        }

        if (! is_numeric($phone)) {
            return $phone;
        }

        return sprintf("(%s)-%s-%s",
            substr($phone, 0, 3),
            substr($phone, 3, 3),
            substr($phone, 6, 4));;
    }
}

if (!function_exists('format_number_to_words')) {
    function format_number_to_words(int $number)
    {
        $numberFormatter = new NumberFormatter("en", NumberFormatter::SPELLOUT);
        return $numberFormatter->format($number);
    }
}