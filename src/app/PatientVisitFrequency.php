<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PatientVisitFrequency extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];

    public static function getId($name)
    {
        return static::select('id')->where('name', $name)->firstOrFail()['id'];
    }

    /**
     * Get the name of the frequency of visits by ID
     *
     * @param int $id
     * @return string
     */
    public static function getNameById($id)
    {
        $cacheKey = "patient_visit_frequency:name_{$id}";

        return Cache::rememberForever($cacheKey, function () use ($id) {
            return static::where('id', $id)->firstOrFail()['name'];
        });
    }

    public static function getTwiceAWeekId()
    {
        return Cache::rememberForever('patient_visit_frequency:twice_a_week_id', function () {
            return static::getId('Twice a week');
        });
    }

    public static function getWeeklyId()
    {
        return Cache::rememberForever('patient_visit_frequency:weekly_id', function () {
            return static::getId('Weekly');
        });
    }

    public static function getBiweeklyId()
    {
        return Cache::rememberForever('patient_visit_frequency:biweekly_id', function () {
            return static::getId('Biweekly');
        });
    }

    public static function getMonthlyId()
    {
        return Cache::rememberForever('patient_visit_frequency:monthly_id', function () {
            return static::getId('Monthly');
        });
    }
}
