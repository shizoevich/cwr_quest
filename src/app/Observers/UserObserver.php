<?php

namespace App\Observers;

use App\Jobs\GenerateUserSignature;
use App\Provider;
use App\User;
use App\UserMeta;

class UserObserver
{
    /**
     * @param User $user
     */
    public function created(User $user)
    {
        UserMeta::query()->firstOrCreate(['user_id' => $user->getKey()]);
        if (null !== $user->provider_id) {
            dispatch(new GenerateUserSignature($user->id));
        }
    }

    /**
     * @param User $user
     */
    public function  deleted(User $user)
    {
        Provider::withTrashed()
                ->where('id', $user->provider_id)
                ->update(['deleted_at' => NULL]);
    }
}
