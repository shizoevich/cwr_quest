<?php


namespace App\Http\Controllers\Api\Tridiuum;

use App\Http\Controllers\Controller;
use App\Option;
use Google_Client;
use Google_Service_Gmail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GmailApiTokenController extends Controller
{
    public function store() 
    {
        $client = new Google_Client();
        $client->setApplicationName(config('app.name'));
        $client->setScopes(Google_Service_Gmail::MAIL_GOOGLE_COM);
        $client->setAuthConfig(json_decode(Option::getOptionValue('gmail_api_credentials'), true));
        // Exchange authorization code for an access token.
        $authCode = $_GET['code'];
        $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
        $client->setAccessToken($accessToken);
        // Check to see if there was an error.
        if (array_key_exists('error', $accessToken)) {
            throw new Exception(join(', ', $accessToken));
        }
        //save gmail api token
        $tokenGmailApi = Option::where(['option_name' => 'gmail_api_token'])->first();
        $tokenGmailApi->option_value = json_encode($client->getAccessToken());
        $tokenGmailApi->save();
        return response()->json('Created new gmail api token', JsonResponse::HTTP_CREATED);
    }
}
