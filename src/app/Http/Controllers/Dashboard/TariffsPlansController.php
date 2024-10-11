<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Requests\StoreInsurancesPlanesPrices;
use App\Http\Requests\StoreTariffPlan;
use App\Jobs\Salary\UpdateSalaryDataWhenFeePerMissingPnChanged;
use App\Jobs\Salary\UpdateSalaryDataWhenFeeScheduleChanged;
use App\PatientInsurance;
use App\PatientInsurancePlan;
use App\PatientInsurancePlanProcedure;
use App\PatientInsuranceProcedure;
use App\TariffPlan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Validator;

class TariffsPlansController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(Auth::user()->isSecretary()) {
            abort(403);
        }
        $tariffPlan = TariffPlan::with('providers')
            ->get();

        return view('dashboard.tariffs-plans.index', [
            'tariffsPlans' => $tariffPlan,
        ]);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreTariffPlan|Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTariffPlan $request)
    {
        if(Auth::user()->isSecretary()) {
            abort(403);
        }

        if ($request->filled('tariff_plan_id')) {
            $oldTariffPlan = TariffPlan::find($request->get('tariff_plan_id'));
            $tariffPlan = TariffPlan::create([
                'name' => $request->get('name'),
                'fee_per_missing_pn' => $oldTariffPlan->fee_per_missing_pn,
            ]);

            $oldTariffPlan->prices->each(function ($price) use ($oldTariffPlan, $tariffPlan) {
                PatientInsurancePlanProcedure::create([
                    'tariff_plan_id' => $tariffPlan->id,
                    'plan_id' => $price->plan_id,
                    'procedure_id' => $price->procedure_id,
                    'price' => $price->price,
                    'telehealth_price' => $price->telehealth_price,
                    'type' => $price->type,
                ]);
            });
        } else {
            $tariffPlan = TariffPlan::create([
                'name' => $request->get('name'),
            ]);
        }

        return redirect(url('dashboard/tariffs-plans', ['id' => $tariffPlan->id]));
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if(Auth::user()->isSecretary()) {
            abort(403);
        }
        $tariffPlan = TariffPlan::with('prices')
            ->findOrFail($id);
        $insurances = PatientInsurance::with(['plans.childPlans'])
            ->get();
        $allProcedures = PatientInsuranceProcedure::all();

        return view('dashboard.tariffs-plans.show', [
            'tariffPlan' => $tariffPlan,
            'insurances' => $insurances,
            'procedures' => $allProcedures,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
    }
    
    /**
     * @param Request $request
     * @param         $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        if(Auth::user()->isSecretary()) {
            abort(403);
        }
        
        $data = $request->toArray();
        if(array_key_exists('fee_per_missing_pn', $data)) {
            $validator = Validator::make($request->all(), [
                'password' => 'required|string|max:255',
                'date_from' => 'nullable|date_format:"m/d/Y"',
            ]);
            if($validator->fails()) {
                return response()->json($validator->messages(), 422);
            }
            if(!\Hash::check($request->input('password'), Auth::user()->password)) {
                return response()->json(['password' => ['The password is invalid.']], 422);
            }
            
            $data['fee_per_missing_pn'] = (float)$data['fee_per_missing_pn'];
        }
        $tariffPlan = TariffPlan::findOrFail($id);
        $oldFeePerMissingPn = $tariffPlan->fee_per_missing_pn;
        $result = $tariffPlan->update($data);
        if($oldFeePerMissingPn != $tariffPlan->fee_per_missing_pn) {
            $dateFrom = Carbon::today();
            if($request->input('date_from')) {
                $dateFrom = Carbon::parse($request->input('date_from'));
            }
            dispatch(new UpdateSalaryDataWhenFeePerMissingPnChanged($tariffPlan->id, $dateFrom));
        }

        return response()->json([
            'result' => $result,
            'TariffPlan' => $tariffPlan,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(Auth::user()->isSecretary()) {
            abort(403);
        }
        $tariffPlan = TariffPlan::findOrFail($id);
        $tariffPlan->prices()->delete();

        $tariffPlan->delete();

        return redirect(url('dashboard/tariffs-plans'));
    }


    public function storePrices(StoreInsurancesPlanesPrices $request)
    {
        if(Auth::user()->isSecretary()) {
            abort(403);
        }
        if(!\Hash::check($request->input('password'), Auth::user()->password)) {
            return response()->json(['password' => ['The password is invalid.']], 422);
        }
        $prices = $request->get('prices');

        if(count($prices) == 0) {
            return response()->json(['result' => true]);
        }

        foreach ($prices as $price) {
            $childPlans = PatientInsurancePlan::where('parent_id', $price['plan_id'])->pluck('id');
            $data = [];
            if(array_key_exists('price', $price)) {
                $data['price'] = $price['price'];
            }
            if(array_key_exists('telehealth_price', $price)) {
                $data['telehealth_price'] = $price['telehealth_price'];
            }
            $parentPlan = PatientInsurancePlanProcedure::updateOrCreate(
                [
                    'plan_id' => $price['plan_id'],
                    'tariff_plan_id' => $price['tariff_plan_id'],
                    'procedure_id' => $price['procedure_id'],
                    'type' => $price['type'],
                ],
                $data
            );

            foreach ($childPlans as $childPlanId) {
                PatientInsurancePlanProcedure::updateOrCreate(
                    [
                        'plan_id' => $childPlanId,
                        'tariff_plan_id' => $price['tariff_plan_id'],
                        'procedure_id' => $price['procedure_id'],
                        'type' => $price['type'],
                    ],
                    [
                        'price' => $parentPlan->price,
                        'telehealth_price' => $parentPlan->telehealth_price,
                    ]
                );
            }
        }
        
        $dateFrom = Carbon::today();
        if($request->input('date_from')) {
            $dateFrom = Carbon::parse($request->input('date_from'));
        }
        dispatch(new UpdateSalaryDataWhenFeeScheduleChanged($prices, $dateFrom));

        return response()->json(['result' => true]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function groupInsurancePlans(Request $request)
    {
        if(Auth::user()->isSecretary()) {
            abort(403);
        }
        $parentPlan = PatientInsurancePlan::firstOrCreate([
            'name' => $request->get('name'),
            'insurance_id' => $request->get('insurance_id'),
        ]);
        $parentPlan->update([
            'parent_id' => null
        ]);

        $parentPlanPrices = PatientInsurancePlanProcedure::where('plan_id', $parentPlan->id)->get();

        $childIds = explode(',', $request->get('plans_ids'));
        foreach ($childIds as $id) {
            $plan = PatientInsurancePlan::find($id);
            if ($id != $parentPlan->id) {
                $plan->update([
                    'parent_id' => $parentPlan->id,
                ]);
                $plan->proceduresPrices()->delete();
//                $plan->proceduresPrices()->saveMany($parentPlanPrices);
                $newPrices = [];
                foreach ($parentPlanPrices as $parentPlanPrice) {
                    $newPrices[] = new PatientInsurancePlanProcedure([
                        'tariff_plan_id'=>$parentPlanPrice['tariff_plan_id'],
                        'plan_id'=>$plan->id,
                        'procedure_id'=>$parentPlanPrice['procedure_id'],
                        'price'=>$parentPlanPrice['price'],
                        'telehealth_price'=>$parentPlanPrice['telehealth_price'],
                        'type'=>$parentPlanPrice['type'],
                    ]);
                }
                $plan->proceduresPrices()->saveMany($newPrices);
            }


            PatientInsurancePlan::where(['parent_id'=> $id])->get()->each(function($subPlan) use ($parentPlan,$parentPlanPrices) {
                $subPlan->update([
                    'parent_id' => $parentPlan->id,
                ]);
                $subPlan->proceduresPrices()->delete();
                $newPrices = [];
                foreach ($parentPlanPrices as $parentPlanPrice) {
                    $newPrices[] = new PatientInsurancePlanProcedure([
                        'tariff_plan_id'=>$parentPlanPrice['tariff_plan_id'],
                        'plan_id'=>$subPlan->id,
                        'procedure_id'=>$parentPlanPrice['procedure_id'],
                        'price'=>$parentPlanPrice['price'],
                        'telehealth_price'=>$parentPlanPrice['telehealth_price'],
                        'type'=>$parentPlanPrice['type'],
                    ]);
                }
                $subPlan->proceduresPrices()->saveMany($newPrices);
            });


        }

        return redirect(
            url('/dashboard/tariffs-plans' , $request->get('tariff_plan_id'))
            . '?' . http_build_query([
                'insurance_id' => $request->get('insurance_id')
            ])
        );
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function ungroupInsurancePlans(Request $request)
    {
        if(Auth::user()->isSecretary()) {
            abort(403);
        }
        PatientInsurancePlan::where(['parent_id'=> $request->get('plan_id')])->get()->each(function($subPlan) {
            $subPlan->update([
                'parent_id' => null,
            ]);
        });

        return redirect(
            url('/dashboard/tariffs-plans' , $request->get('tariff_plan_id'))
            . '?' . http_build_query([
                'insurance_id' => $request->get('insurance_id')
            ])
        );
    }
}
