<?php

namespace App\Helpers;

use App\Provider;
use App\Patient;
use App\Models\UphealMeeting;
use App\Services\Upheal\UphealService;
use App\Components\SnoozedNotification\ScheduledNotification;
use App\Notifications\AnonymousNotifiable;
use App\Notifications\UphealSessionNotification;
use App\Mail\Upheal\UphealAccountInvite;
use Carbon\Carbon;

class UphealHelper
{
    public static function createPatient(Provider $provider, Patient $patient)
    {
        $response = UphealService::createPatient(
            $provider->upheal_user_id,
            $patient->first_name,
            $patient->last_name,
            $patient->getEmail()
        );

        $patient->update([
            'upheal_user_id' => $response['userId'],
            'upheal_client_session_url' => $response['clientSessionUrl'],
            'upheal_therapist_session_url' => $response['therapistSessionUrl']
        ]);
    }

    public static function sendNotification(
        UphealMeeting $uphealMeeting,
        Carbon $date,
        $viaEmail,
        $viaSecondaryEmail,
        $viaSms,
        $phone = null,
        $email = null,
        $secondaryEmail = null
    ) {
        $target = (new AnonymousNotifiable);

        if (!empty($email) && $viaEmail) {
            $target->route('mail', $email);
        }
      
        if (!empty($phone) && $viaSms) {
            $target->route('ringcentral_sms', $phone);
        }

        $notification = ScheduledNotification::create(
            $target,
            new UphealSessionNotification($uphealMeeting),
            $date->startOfMinute()
        );

        if ($notification->getSendAt()->lte(Carbon::now())) {
            $notification->sendNow();
        }

        if (!empty($secondaryEmail) && $viaSecondaryEmail) {
            $target2 = (new AnonymousNotifiable);
            $target2->route('mail',  $secondaryEmail);
            $notification2 = ScheduledNotification::create(
                $target2,
                new UphealSessionNotification($uphealMeeting),
                $date->startOfMinute()
            );
            if ($notification2->getSendAt()->lte(Carbon::now())) {
                $notification2->sendNow();
            }
        }
    }

    public static function createProvider(Provider $provider)
    {
        $providerUser = $provider->user;
        if (empty($providerUser)) {
            return;
        }

        $providerFirstName = $provider->first_name;
        $providerLastName = $provider->last_name;

        if (!$providerFirstName || !$providerLastName) {
            $providerNameParts = explode(',', $provider->provider_name);

            if (!$providerFirstName) {
                $providerFirstName = trim($providerNameParts[0]);
            }

            if (!$providerLastName && isset($providerNameParts[1])) {
                $providerLastName = trim($providerNameParts[1]);
            }
        }

        $response = UphealService::createProvider($providerFirstName, $providerLastName, $providerUser->email);

        $provider->update([
            'upheal_user_id' => $response['userId'],
            'upheal_invite_url' => $response['inviteUrl'],
            'upheal_private_room_link' => $response['privateRoomLink'],
        ]);

        \Mail::to($providerUser->email)->send(new UphealAccountInvite($provider->provider_name, $provider->upheal_invite_url));
    }
}
