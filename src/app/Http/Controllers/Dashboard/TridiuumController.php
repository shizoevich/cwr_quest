<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Provider;
use Illuminate\Http\Request;

class TridiuumController extends Controller
{
    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProviderInvalidCredentials(Request $request)
    {
        $providers = Provider::query()
            ->select([
                'providers.id',
                'users.id AS user_id',
                'providers.provider_name',
                'providers.tridiuum_credentials_failed_at'
            ])
            ->join('users', 'users.provider_id', '=', 'providers.id')
            ->join('users_meta', 'users.id', '=', 'users_meta.user_id')
            ->where('users_meta.has_access_rights_to_reassign_page', false)
            ->whereNotNull('providers.tridiuum_credentials_failed_at')
            ->orderBy('providers.provider_name')
            ->get();
        $messages = [];
        $providers->each(function(Provider $provider) use (&$messages) {
            $messages[] = trans('alerts.invalid_tridiuum_credentials', [
                'provider_name' => $provider->provider_name,
                'user_id' => $provider->user_id,
            ]);
        });

        return response()->json([
            'status' => false,
            'messages' => $messages
        ]);
    }
}