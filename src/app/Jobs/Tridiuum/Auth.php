<?php

namespace App\Jobs\Tridiuum;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use function GuzzleHttp\json_decode;

class Auth implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $login;
    private $password;
    private $checkAuth;

    /**
     * Create a new job instance.
     *
     * @param $login
     * @param $password
     * @param bool $checkAuth
     */
    public function __construct($login, $password, $checkAuth=false)
    {
        $this->login = $login;
        $this->password = $password;
        $this->checkAuth = $checkAuth;
    }

    /**
     * Execute the job.
     *
     * @return Client|bool
     */
    public function handle()
    {
        $jar = new CookieJar;
        $client = new Client(['cookies' => $jar]);
        $client->request('POST', 'https://polestarapp.com/admin/login',[
            'form_params' => [
                'user[login]' => $this->login,
                'user[password]' => $this->password
            ]
        ]);
        if($this->checkAuth) {
            $response = $client->request('GET', 'https://polestarapp.com/check_authentication');

            return $response->getStatusCode() == 204;
        }
        return $client;
    }
}
