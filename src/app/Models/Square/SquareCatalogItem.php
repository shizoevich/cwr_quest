<?php

namespace App\Models\Square;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SquareCatalogItem extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'external_id',
        'name',
    ];

    public static function getCopayItemId()
    {
        return \Cache::rememberForever('square_catalog_items:copay_id', function () {
            return static::where('name', 'like', '%co-pay%')->first()['id'];
        });
    }

    public static function getDeductibleItemId()
    {
        return \Cache::rememberForever('square_catalog_items:deductible_id', function () {
            return static::where('name', 'like', '%deductible%')->first()['id'];
        });
    }

    public static function getSelfpayItemId()
    {
        return \Cache::rememberForever('square_catalog_items:selfpay_id', function () {
            return static::where('name', 'like', '%self-pay%')->first()['id'];
        });
    }

    public static function getCancellationFeeItemId()
    {
        return \Cache::rememberForever('square_catalog_items:cancellation_fee_id', function () {
            return static::where('name', 'like', '%charge for cancellation%')->first()['id'];
        });
    }

    public static function getCopayItemExternalId()
    {
        return \Cache::rememberForever('square_catalog_items:copay_external_id', function () {
            return static::where('name', 'like', '%co-pay%')->first()['external_id'];
        });
    }

    public static function getDeductibleItemExternalId()
    {
        return \Cache::rememberForever('square_catalog_items:deductible_external_id', function () {
            return static::where('name', 'like', '%deductible%')->first()['external_id'];
        });
    }

    public static function getSelfpayItemExternalId()
    {
        return \Cache::rememberForever('square_catalog_items:selfpay_external_id', function () {
            return static::where('name', 'like', '%self-pay%')->first()['external_id'];
        });
    }

    public static function getCancellationFeeItemExternalId()
    {
        return \Cache::rememberForever('square_catalog_items:cancellation_fee_external_id', function () {
            return static::where('name', 'like', '%charge for cancellation%')->first()['external_id'];
        });
    }

    public static function getCopayItem()
    {
        return \Cache::rememberForever('square_catalog_items:copay', function () {
            return static::where('name', 'like', '%co-pay%')->first();
        });
    }

    public static function getDeductibleItem()
    {
        return \Cache::rememberForever('square_catalog_items:deductible', function () {
            return static::where('name', 'like', '%deductible%')->first();
        });
    }

    public static function getSelfpayItem()
    {
        return \Cache::rememberForever('square_catalog_items:selfpay', function () {
            return static::where('name', 'like', '%self-pay%')->first();
        });
    }

    public static function getCancellationFeeItem()
    {
        return \Cache::rememberForever('square_catalog_items:cancellation_fee', function () {
            return static::where('name', 'like', '%charge for cancellation%')->first();
        });
    }

    public static function getCashItems(): array
    {
        return [
            static::getSelfpayItem(),
            static::getCancellationFeeItem(),
        ];
    }

    public static function getInsuranceItems(): array
    {
        return [
            static::getCopayItem(),
            static::getDeductibleItem(),
            static::getCancellationFeeItem(),
        ];
    }
}
