<?php

$config = [
    'mode' => env('SQUARE_MODE'),
];

if(env('SQUARE_MODE') == 'sandbox') {
    $config['application_id'] = env('SQUARE_SANDBOX_APPLICATION_ID');
    $config['access_token'] = env('SQUARE_SANDBOX_PERSONAL_ACCESS_TOKEN');
    $config['sdk_url'] = env('SQUARE_SANDBOX_SDK_URL');
} else {
    $config['application_id'] = env('SQUARE_APPLICATION_ID');
    $config['access_token'] = env('SQUARE_PERSONAL_ACCESS_TOKEN');
    $config['sdk_url'] = env('SQUARE_SDK_URL');
}
$config['location_id'] = env('SQUARE_FORM_LOCATION_ID');

return $config;