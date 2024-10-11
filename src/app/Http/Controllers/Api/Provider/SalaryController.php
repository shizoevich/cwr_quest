<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Http\Requests\Provider\Salary\AdditionalCompensation as AdditionalCompensationRequest;
use App\Http\Requests\Provider\Salary\StoreAdditionalCompensation as StoreAdditionalCompensationRequest;
use App\Models\Billing\BillingPeriod;
use App\Models\Provider\Salary;
use App\Provider;
use Carbon\Carbon;

class SalaryController extends Controller
{
    public function additionalCompensation(AdditionalCompensationRequest $request, Provider $provider)
    {
        $types = [];
        $additionalCompensations = Salary::query()
            ->select([
                '*',
                \DB::raw('TRUE AS exist'),
                \DB::raw('FALSE AS is_other'),
            ])
            ->where('provider_id', $provider->getKey())
            ->where('billing_period_id', $request->input('billing_period_id'))
            ->whereIn('type', Salary::ADDITIONAL_COMPENSATION_TYPES)
            ->get();
        
        foreach (Salary::ADDITIONAL_COMPENSATION_TYPES as $slug => $type) {
            if($type === Salary::TYPE_OTHER_COMPENSATION) {
                continue;
            }
            $additionalCompensation = $additionalCompensations->where('type', $type)->first();
            if($additionalCompensation) {
                $additionalCompensation->title = Salary::getTitleByType($type);
                $additionalCompensation->slug = $slug;
                $additionalCompensation->additional_data = $additionalCompensation->additional_data ?? ['visit_count' => null];
                $types[] = $additionalCompensation->toArray();
            } else {
                $types[] = [
                    'exist' => false,
                    'slug' => $slug,
                    'title' => Salary::getTitleByType($type),
                    'type' => $type,
                    'paid_fee' => null,
                    'notes' => null,
                    'additional_data' => ['visit_count' => null],
                ];
            }
        }
        $otherSlug = Salary::getSlugByType(Salary::TYPE_OTHER_COMPENSATION);
        $otherTitle = Salary::getTitleByType(Salary::TYPE_OTHER_COMPENSATION);
        $otherCompensation = $additionalCompensations
            ->where('type', Salary::TYPE_OTHER_COMPENSATION)
            ->each(function($item) use ($otherSlug, $otherTitle) {
                $item->slug = $otherSlug;
                $item->title = $otherTitle;
                $item->is_other = true;
                $item->additional_data = $item->additional_data ?? ['visit_count' => null];
                
                return $item;
            })
            ->toArray();
        $types = array_merge($types, $otherCompensation);
        
        return response()->json([
            'data' => $types,
            'billing_period' => BillingPeriod::query()->where('id', $request->input('billing_period_id'))->with('type')->first(),
        ]);
    }
    
    public function storeAdditionalCompensation(StoreAdditionalCompensationRequest $request, Provider $provider)
    {
        $additionalCompensation = collect($request->input('additional_compensation'));
        $existingSalaryIds = $additionalCompensation->where('id', '!=', null)->where('paid_fee', '!=', 0)->pluck('id')->unique();
        Salary::query()
            ->where('provider_id', $provider->getKey())
            ->where('billing_period_id', $request->input('billing_period_id'))
            ->whereIn('type', Salary::ADDITIONAL_COMPENSATION_TYPES)
            ->when($existingSalaryIds, function($query, $existingSalaryIds) {
                $query->whereNotIn('id', $existingSalaryIds);
            })
            ->delete();
        $additionalCompensation->whereIn('id', $existingSalaryIds)->each(function($item) use ($provider, $request) {
            Salary::query()->whereKey($item['id'])->update([
                'fee' => $item['paid_fee'] * 100,
                'paid_fee' => $item['paid_fee'] * 100,
                'notes' => $item['notes'],
                'additional_data' => !empty($item['additional_data']) ? json_encode($item['additional_data']) : ['visit_count' => null],
            ]);
        });
        $additionalCompensation->where('id', '=', null)->each(function($item) use ($provider, $request) {
            $isOther = data_get($item, 'is_other');
            if ($item['paid_fee'] <= 0 && !$isOther) {
                return;
            }
            if ($isOther) {
                $item['type'] = Salary::TYPE_OTHER_COMPENSATION;
            }
            Salary::query()->create([
                'provider_id' => $provider->getKey(),
                'type' => $item['type'],
                'fee' => $item['paid_fee'] * 100,
                'paid_fee' => $item['paid_fee'] * 100,
                'billing_period_id' => $request->input('billing_period_id'),
                'date' => Carbon::now()->toDateString(),
                'notes' => $item['notes'],
                'additional_data' => !empty($item['additional_data']) ? $item['additional_data'] : ['visit_count' => null],
            ]);
        });
        
        return response()->json(null, 204);
    }
}
