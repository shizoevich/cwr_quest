<?php


namespace App\Http\Controllers\Api\Tridiuum;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tridiuum\Provider\Assign;
use App\Http\Requests\Tridiuum\Provider\Index;
use App\Models\TridiuumProvider;
use App\Provider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProviderController extends Controller
{
    /**
     * @param Index $request
     *
     * @return JsonResponse
     */
    public function index(Index $request)
    {
        $providers = TridiuumProvider::query()
            ->select([
                'id',
                \DB::raw("CONCAT(`first_name`, ' ', `last_name`) AS provider_name")
            ])
            ->when($request->has('only_unassigned') && $request->input('only_unassigned'), function($query) {
                $query->whereNull('internal_id');
            })
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
        
        return response()->json(['providers' => $providers]);
    }
    
    public function assign(Assign $request, Provider $provider)
    {
        if($request->input('tridiuum_provider_id')) {
            $tridiuumProvider = TridiuumProvider::find($request->input('tridiuum_provider_id'));
            if($tridiuumProvider->internal_id !== null) {
                //copied from App\Http\Controllers\Dashboard\DoctorsController@saveDoctorProviderRelation
                return response()->json([
                    'success' => false,
                    'errorMessage' => 'This Tridiuum account has already been assigned to another provider.',
                ]);
            }
            TridiuumProvider::query()->where('internal_id', $provider->getKey())->update(['internal_id' => null]);
            $tridiuumProvider->internal_id = $provider->getKey();
            $tridiuumProvider->save();
        } else {
            TridiuumProvider::query()->where('internal_id', $provider->getKey())->update(['internal_id' => null]);
        }
        
        //copied from App\Http\Controllers\Dashboard\DoctorsController@saveDoctorProviderRelation
        return response()->json([
            'success' => true
        ], 201);
    }
}