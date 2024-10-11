<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Diagnose\Autocomplete;
use App\Models\Diagnose;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DiagnoseController extends Controller
{
    /**
     * @param Autocomplete $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function autocomplete(Autocomplete $request)
    {
        $diagnoses = Diagnose::query()
            ->select([
                'id',
                'code',
                'description',
            ])
            ->when($request->input('q'), function(Builder $query, $q) {
                $query->where(function(Builder $query) use ($q) {
                    $query->where('code', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%");
                });
            })
            ->whereNull('terminated_at')
            ->where('is_custom', 0)
            ->where('is_billable', 1)
            ->orderBy('code')
            ->limit(10)
            ->get();
        
        
        return response()->json([
            'diagnoses' => $diagnoses,
        ]);
    }
}
