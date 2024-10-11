<?php

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
Broadcast::channel('availabilityFor.{providerId}', function ($user, $providerId) {
    return !empty($user->provider_id) && $user->provider_id == $providerId;
});

Broadcast::channel('availabilityFor', function ($user) {
    return $user->isAdmin();
});

Broadcast::channel('parsers', function ($user) {
    return $user->isAdmin(); 
});

Broadcast::channel('appointments', function ($user) {
    return $user->isAdmin();
});

Broadcast::channel('removal-requests', function ($user) {
    return $user->isAdmin();
});

Broadcast::channel('tridiuum-appointments', function ($user) {
    return $user->isAdmin();
});

Broadcast::channel('providers.{provider_id}.appointments', function ($user, $providerId) {
    return !empty($user->provider_id) && $user->provider_id == $providerId;
});

Broadcast::channel('users.{user_id}.ring-out.{call_log_id}', function ($user, $userId, $callLogId) {
    return (int)$user->id === (int)$userId;
});

Broadcast::channel('zip-archive.{user_id}', function ($user, $userId) {
    return $user->isAdmin() && ((int)$user->id === (int)$userId);
});
