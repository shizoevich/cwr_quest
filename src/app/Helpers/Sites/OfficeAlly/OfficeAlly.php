<?php

namespace App\Helpers\Sites\OfficeAlly;

use App\Helpers\ExceptionNotificator;
use App\Models\Officeally\OfficeAllyCookie;
use App\Option;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use App\Notifications\AnErrorOccurred;
use App\Helpers\Sites\Loggers\OfficeAllyRequestsLogger;
use App\Exceptions\Officeally\OfficeallyAuthenticationException;
use Throwable;

/**
 * Class OfficeAlly
 * @package App\Helpers\Sites
 */
class OfficeAlly
{
    /** @var string */
    private $login;
    
    /** @var string */
    private $password;
    
    /** @var string */
    private $accountName;
    
    /** @var Client */
    private $client;
    
    /**
     * OfficeAlly constructor.
     *
     * @param string $accountName
     */
    public function __construct(string $accountName)
    {
        $this->accountName = $accountName;
        $this->initCredentials();
        $this->initOfficeAllyCookies();
    }
    
    /**
     * Initialize OfficeAlly access credentials.
     */
    private function initCredentials()
    {
        $credentials = Option::getParserConfig($this->accountName);
        $this->login = $credentials['login'];
        $this->password = $credentials['password'];
    }
    
    /**
     * Initialize OfficeAlly cookies.
     */
    private function initOfficeAllyCookies()
    {
        $cookiesModel = OfficeAllyCookie::query()
            ->where('account_name', $this->accountName)
            ->latest('updated_at')
            ->first();
        $this->initClient($cookiesModel);
    }
    
    /**
     * @return Client
     */
    private function getClient(): Client
    {
        if(!isset($this->client)) {
            $this->initClient();
        }
        
        return $this->client;
    }
    
    /**
     * @param OfficeAllyCookie|null $cookiesModel
     *
     * @return Client
     */
    private function initClient($cookiesModel = null): Client
    {
        /** @var OfficeAllyCookie $cookiesModel */
        $cookies = null;
        if($cookiesModel && $cookiesModel->cookies) {
            $cookies = CookieJar::fromArray($cookiesModel->cookies, 'pm.officeally.com');
        }
        $clientConfig = [
            'cookies'         => $cookies ?? true,
            'verify'          => false,
            'base_uri'        => 'https://pm.officeally.com/pm/',
            'allow_redirects' => false,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.81 Safari/537.36'
            ],
            'handler' => with(new OfficeAllyRequestsLogger())->createLoggingHandlerStack(),
        ];
        
        if(config('officeally.proxy_enabled')) {
            $clientConfig['proxy'] = config('officeally.proxy');
        }

        $this->client = new Client($clientConfig);
        
        return $this->client;
    }
    
    /**
     * @param Throwable|string $exception
     */
    public function notifyIfFailed($exception, $tags = [])
    {
        with(new ExceptionNotificator())->officeAllyNotifyAndSendToSentry(new AnErrorOccurred($exception), $tags);
    }
    
    /**
     * @return bool
     */
    public function login()
    {
        $response = $this->post('CommonUserControls/Ajax/AjaxLogin.aspx', [
            'json' => [
                'accessReason' => '',
                'domain'       => 2,
                'isEmergency'  => 'False',
                'offset'       => '3',
                'timezone'     => 'Middle East Standard Time',
                'user'         => [
                    'Username' => $this->login ,
                    'Password' => $this->password,
                ]
            ],
        ]);
        $response = json_decode($response->getBody()->getContents(), true);
      
        if (data_get($response, 'Status') !== 200 || empty(data_get($response, 'dt'))) {
            return false;
        }
        $dt = data_get($response, 'dt');
        $this->get($dt);
        $oldCookies = $this->getClient()->getConfig('cookies')->toArray();
        $newCookies = [];
        foreach ($oldCookies as $item) {
            $newCookies[$item['Name']] = $item['Value'];
        }
        OfficeAllyCookie::query()->where('account_name', $this->accountName)->delete();
        $cookiesModel = OfficeAllyCookie::create([
            'account_name' => $this->accountName,
            'cookies' => $newCookies,
        ]);
        $this->initClient($cookiesModel);
        $this->get("Default.aspx");
        
        return true;
    }

    /**
     * @return bool
     */
    public function loginWithPuppeteer()
    {
        $command = 'node ' . base_path() . '/puppeteer-scripts/' . config('officeally.login_script') . ' ' . $this->login . ' ' . $this->password;
        $output = [];
        $returnValue = null;
        exec($command, $output, $returnValue);

        if ($returnValue !== 0 || !count($output)) { 
            return false;
        }

        $newCookies = [];
        try {
            $oldCookies = json_decode($output[0], true);
            foreach ($oldCookies as $item) {
                $newCookies[$item['name']] = $item['value'];
            }
        } catch (\Exception $e) {
            //
        }

        if (!count($newCookies)) {
            return false;
        }
        
        OfficeAllyCookie::query()->where('account_name', $this->accountName)->delete();
        $cookiesModel = OfficeAllyCookie::create([
            'account_name' => $this->accountName,
            'cookies' => $newCookies,
        ]);
        $this->initClient($cookiesModel);
        $this->get("Default.aspx");
        
        return true;
    }

    
    /**
     * @param string $uri
     * @param array  $options
     * @param bool   $needAuthorization
     * @param bool   $notifyIfUndefinedRedirect
     * @param int    $loginAttempts
     *
     * @return ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function post(string $uri, array $options = [], bool $needAuthorization = false, bool $notifyIfUndefinedRedirect = true, int $loginAttempts = 0)
    {
        try {
            $request = new Request('POST', $uri);

            // logic to prevent excessive requests to office ally
            if(\Cache::get('office_ally_authentication_failed')) {
                throw new OfficeallyAuthenticationException("[$this->accountName] Authentication failed.", $request, null);
            }

            try {
                $response = $this->getClient()->send($request, $options);
            } catch (ConnectException $e) {
                throw new OfficeallyAuthenticationException("[$this->accountName] Connection failed.", $request, null);
            } catch (RequestException $e) {
                $response = $e->getResponse();
                if(isset($response) && in_array($response->getStatusCode(), [502, 503, 504])) {
                    throw new OfficeallyAuthenticationException("[$this->accountName] Connection failed.", $request, null);
                } else {
                    throw $e;
                }
            }

            $this->handleDefaultRedirect($uri, $response);

            if($needAuthorization && $loginAttempts > 1) {
                \Cache::remember('office_ally_authentication_failed', 5, function() {
                    return true;
                });
                throw new OfficeallyAuthenticationException("[$this->accountName] Authentication failed.", $request, $response);
            }
            if($needAuthorization && !$this->isResponseAuthenticated($response, $uri, $notifyIfUndefinedRedirect)) {
                $this->loginWithPuppeteer();
            
                return $this->post($uri, $options, true, $notifyIfUndefinedRedirect, ++$loginAttempts);
            }
        
            return $response;
        } catch (OfficeallyAuthenticationException $e) {
            $this->notifyIfFailed($e, ['office_ally' => 'emergency']);
            throw $e;
        } catch (RequestException $e) {
            $this->notifyIfFailed($e);
            throw $e;
        }
    }
    
    /**
     * @param string $uri
     * @param array  $options
     * @param bool   $needAuthorization
     * @param bool   $notifyIfUndefinedRedirect
     * @param int    $loginAttempts
     *
     * @return ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function get(string $uri, array $options = [], bool $needAuthorization = false, bool $notifyIfUndefinedRedirect = true, int $loginAttempts = 0)
    {
        try {
            $request = new Request('GET', $uri);

            // logic to prevent excessive requests to office ally
            if(\Cache::get('office_ally_authentication_failed')) {
                throw new OfficeallyAuthenticationException("[$this->accountName] Authentication failed.", $request, null);
            }

            try {
                $response = $this->getClient()->send($request, $options);
            } catch (ConnectException $e) {
                throw new OfficeallyAuthenticationException("[$this->accountName] Connection failed.", $request, null);
            } catch (RequestException $e) {
                $response = $e->getResponse();
                if(isset($response) && in_array($response->getStatusCode(), [502, 503, 504])) {
                    throw new OfficeallyAuthenticationException("[$this->accountName] Connection failed.", $request, null);
                } else {
                    throw $e;
                }
            }

            $this->handleDefaultRedirect($uri, $response);

            if($needAuthorization && $loginAttempts > 1) {
                \Cache::remember('office_ally_authentication_failed', 5, function() {
                    return true;
                });
                throw new OfficeallyAuthenticationException("[$this->accountName] Authentication failed.", $request, $response);
            }
            if($needAuthorization && !$this->isResponseAuthenticated($response, $uri, $notifyIfUndefinedRedirect)) {
                $this->loginWithPuppeteer();
            
                return $this->get($uri, $options, true, $notifyIfUndefinedRedirect, ++$loginAttempts);
            }
        
            return $response;
        } catch (OfficeallyAuthenticationException $e) {
            $this->notifyIfFailed($e, ['office_ally' => 'emergency']);
            throw $e;
        } catch (RequestException $e) {
            $this->notifyIfFailed($e);
            throw $e;
        }
    }

    private function handleDefaultRedirect(string $originalUri, ResponseInterface $response)
    {
        if (strpos($originalUri, 'Default.aspx') !== false) {
            return;
        }

        $locationHeader = data_get($response->getHeader('Location'), '0');
        if (!$locationHeader) {
            return;
        }

        if (strpos($locationHeader, 'Default.aspx') !== false) {
            $this->get("Default.aspx");
        }
    }
    
    /**
     * @param ResponseInterface $response
     *
     * @param string            $uri
     * @param bool              $notifyIfUndefinedRedirect
     *
     * @return bool
     */
    private function isResponseAuthenticated(ResponseInterface $response, string $uri, bool $notifyIfUndefinedRedirect = true): bool
    {
        $locationHeader = data_get($response->getHeader('Location'), '0');
        if(!$locationHeader) {
            return true;
        }
        $isUnauthenticated = str_contains($locationHeader, 'Login.aspx');
        $isMaintenance = str_contains($locationHeader, 'SiteMaintenance.aspx');
        if($isMaintenance && !\Cache::get('officeally_maintenance_notification')) {
            $this->notifyIfFailed("[MAINTENANCE] Office Ally is temporarily unavailable while we make important upgrades to our site.", ['office_ally' => 'emergency']);
            \Cache::remember('officeally_maintenance_notification', 10, function() {
                return true;
            });
        }
        if($notifyIfUndefinedRedirect && !$isUnauthenticated && !$isMaintenance) {
            $this->notifyIfFailed("[{$uri}] Undefined Redirect to {$locationHeader}", ['office_ally' => 'emergency']);
        }
        
        return !$isUnauthenticated;
    }
}
