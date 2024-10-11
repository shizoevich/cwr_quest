<?php

namespace App\Helpers\Sites;

use App\Helpers\ExceptionNotificator;
use App\Helpers\Google\GmailInboxService;
use App\Notifications\AnErrorOccurred;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\TransferStats;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\DomCrawler\Crawler;
use App\Helpers\Sites\Loggers\TridiuumRequestsLogger;
use Throwable;

class Tridiuum
{
    /**
     * Base URI of the client that is merged into relative URIs. Can be a string or instance of UriInterface.
     *
     * @var string|\Psr\Http\Message\UriInterface
     */
    protected $baseUri = 'https://polestarapp.com/';

    /**
     * Client for sending HTTP requests.
     *
     * @var Client
     */
    protected $client;

    /** @var string */
    protected $login;
    /** @var string */
    protected $password;
    /** @var string|null */
    protected $csrfToken = null;

    /**
     * Tridiuum constructor.
     *
     * @param string $login
     * @param string $password
     */
    public function __construct($login, $password)
    {
        $this->login = $login;
        $this->password = $password;

        $guzzleClientOptions = [
            'cookies' => true,
            'verify' => false,
            'base_uri' => $this->baseUri,
            'handler' => with(new TridiuumRequestsLogger())->createLoggingHandlerStack(),
        ];

        $this->client = new Client($guzzleClientOptions);
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param Throwable|string $exception
     */
    public function notifyIfFailed($exception, $tags = [])
    {
        with(new ExceptionNotificator())->tridiuumNotifyAndSendToSentry(new AnErrorOccurred($exception), $tags);
    }

    /**
     * @return bool
     */
    public function login()
    {
        $startTS = (new \DateTime("now", new \DateTimeZone('UTC')))->getTimestamp();
        $response = $this->client->request('POST', 'admin/login', [
            'form_params' => [
                'user[login]' => $this->login,
                'user[password]' => $this->password
            ],
            'on_stats' => function (TransferStats $stats) use (&$responseEffectiveURL) {
                $responseEffectiveURL = $stats->getEffectiveUri();
            },
        ]);

        if($response->getStatusCode() === 503) {
            $this->notifyIfFailed('Tridiuum is currently down for maintenance!', ['tridiuum' => 'emergency']);
            throw new \RuntimeException('Tridiuum is currently down for maintenance!');
        }

        if($this->is2FACheckTriggered($response)) {
            $mfaUserIDList = explode('/', $responseEffectiveURL);
            $mfaUserID = array_pop($mfaUserIDList);
            $bypassResult = $this->bypass2FA($startTS, $mfaUserID);
            if (!$bypassResult && !\Cache::get('tridiuum_2fa_failed_notification')) {
                $this->notifyIfFailed('Cannot login to Tridiuum. 2FA bypass flow failed!', ['tridiuum' => 'emergency']);
                \Cache::remember('tridiuum_2fa_failed_notification', 30, function() {
                    return true;
                });
            }
        }

        if($this->isCredentialsInvalid() && !\Cache::get('tridiuum_credentials_changed_notification')) {
            $this->notifyIfFailed('Cannot login to Tridiuum. Access Credentials is Invalid!', ['tridiuum' => 'emergency']);
            \Cache::remember('tridiuum_credentials_changed_notification', 30, function() {
                return true;
            });
        }

        return tap($this->isLoggedIn(), function ($isLoggedIn) use ($response) {
            $this->csrfToken = null;
            if ($isLoggedIn) {
                $crawler = new Crawler($response->getBody()->getContents());
                $crawler = $crawler->filterXPath('//meta[@name="csrf-token"]/@content');
                if ($crawler->count() > 0) {
                    $this->csrfToken = $crawler->text();
                }
            }
        });
    }

    /**
     * @return bool
     */
    public function isLoggedIn()
    {
        $response = $this->checkAuth();

        return $response->getStatusCode() === 204;
    }

    /**
     * @return bool
     */
    public function isCredentialsInvalid()
    {
        $response = $this->checkAuth();
        $statusCode = $response->getStatusCode();

        /**
         * If not authenticated tridiuum returns response with status code 200:
         * {
         *   "password": null,
         *   "login": null
         * }
         */
        if($statusCode === 200 || $statusCode === 401) {
            return true;
        }

        return false;
    }

    /**
     * @return mixed|ResponseInterface
     */
    public function checkAuth(): ResponseInterface
    {
        $response = $this->client->request('GET', 'check_authentication', [
            RequestOptions::HTTP_ERRORS => false,
        ]);

        return $response;
    }

    /**
     * @return string
     */
    public function getSchedulingPage()
    {
        $response = $this->client->request('GET', 'admin/scheduling');

        return $response->getBody()->getContents();
    }

    /**
     * @return string
     */
    public function getPatientsPage()
    {
        $response = $this->client->request('GET', 'admin/patients');

        return $response->getBody()->getContents();
    }
    
    /**
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getProviders()
    {
        $response = $this->client->request('GET', 'admin/get-geography-based-practitioners?geography_id=all');
    
        return $this->jsonDecode($response);
    }

    /**
     * @param $patientId
     *
     * @return string
     */
    public function getPatientEditPage($patientId)
    {
        $response = $this->client->request('GET', 'admin/patients/' . $patientId . '/edit');

        return $response->getBody()->getContents();
    }

    /**
     * @param $currentTrack
     *
     * @return mixed
     */
    public function getAssessmentsDocuments($currentTrack)
    {
        $response = $this->client->request('GET', 'admin/patients/tracks/' . $currentTrack);

        return $this->jsonDecode($response);
    }

    /**
     * @param $patientId
     *
     * @return string
     */
    public function getPatientDetailTrack($patientId) 
    {
        $response = $this->client->request('GET', 'filters/patient_detail?patient_id=' . $patientId . '&type=track_list');

        return $response->getBody()->getContents();
    }

    /**
     * @param $patientId
     * @param $currentTrack
     *
     * @return mixed
     */
    public function getNotesDocuments($patientId, $currentTrack)
    {
        $response = $this->client->request('GET', 'admin/patients/' . $patientId . '/patient_notes?track_id=' . $currentTrack);

        return $this->jsonDecode($response);
    }

    /**
     * @param $currentTrack
     *
     * @return mixed
     */
    public function downloadDocument($documentId)
    {
        $response = $this->client->request('GET', 'admin/report/' . $documentId);

        return $response->getBody()->getContents();
    }

    /**
     * @return mixed
     */
    public function getUserDetails()
    {
        $response = $this->client->request('GET', 'user_details');

        return $this->jsonDecode($response);
    }

    /**
     * @param int $length
     * @param int $start
     * @param string $search
     *
     * @return mixed
     */
    public function getSites($length = 500, $start = 0, $search = '')
    {
        $response = $this->client->request('GET', 'admin/sites.json', [
            'query' => [
                'site' => 'all',
                'sort_column' => 'name',
                'sort_direction' => 'asc',
                'length' => $length,
                'start' => $start,
                'search' => $search,
                'show_all' => 'false'
            ]
        ]);

        return $this->jsonDecode($response);
    }

    /**
     * @param int $length
     * @param int $start
     * @param string $search
     *
     * @return mixed
     */
    public function getPatients($length = 500, $start = 0, $search = '')
    {
        $response = $this->client->request('GET', 'admin/patients.json', [ 
            'query' => [
                'site' => 'all',
                'type' => 'active',
                'app'=>'datatables',
                'standard_fields' => 'true',
                'show_all' => 'false',
                'sort' => 'last_name',
                'limit' => $length,
                'offset' => $start,
                'search' => $search,
            ]
        ]);

        return $this->jsonDecode($response);
    }

    /**
     * @param string $patient_id
     *
     * @return mixed
     */
    public function getPatientData($patient_id) 
    {
        $response = $this->client->request('GET', 'filters/patient_edit?patient_id='.$patient_id );
        return $this->jsonDecode($response);
    }
    
    /**
     * @param Carbon      $from
     * @param Carbon      $to
     * @param int         $limit
     * @param int         $offset
     * @param string|null $providerId
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getAvailabilities(Carbon $from, Carbon $to, $limit = 500, $offset = 0, string $providerId = null)
    {
        $query = [
            'expand' => 'true',
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'limit' => $limit,
            'offset' => $offset,
        ];
        if($providerId) {
            $query['provider_id'] = $providerId;
        }
        $response = $this->client->request('GET', 'api/v1/availability', [
            'query' => $query,
        ]);

        return $this->jsonDecode($response);
    }
    
    /**
     * @param Carbon      $from
     * @param Carbon      $to
     * @param int         $limit
     * @param int         $offset
     * @param string|null $providerId
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getAppointments(Carbon $from, Carbon $to, $limit = 500, $offset = 0, string $providerId = null) 
    {
        $query = [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'limit' => $limit,
            'offset' => $offset,
        ];
        if($providerId) {
            $query['provider_id'] = $providerId;
        }
        
        $response = $this->client->request('GET', 'api/v1/appointments', [
            'query' => $query
        ]);

        return $this->jsonDecode($response);
    }
    
    /**
     * @param             $siteId
     * @param Carbon      $from
     * @param Carbon      $to
     * @param string|null $providerId
     * @param bool        $virtual
     * @param false       $inPerson
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createAvailability($siteId, Carbon $from, Carbon $to, string $providerId = null, $virtual = true, $inPerson = false)
    {
        $data = [
            'name' => "Intake Availability",
            'event_type' => "availability",
            'except' => [],
            'start_date' => $from->toDateTimeLocalString(),
            'end_date' => $to->toDateTimeLocalString(),
            'site_id' => $siteId,
            'patient' => '',
            'patient_id' => '',
            'event_type_editable' => true,
            'visit_type_code' => 'PSI60',
            'in_person' => $inPerson,
            'virtual' => $virtual,
        ];
        if($providerId) {
            $data['provider_id'] = $providerId;
        }

        $response = $this->client->request('POST', 'api/v1/availability', [
            'headers' => [
                'X-CSRF-Token' => $this->csrfToken
            ],
            'json' => $data
        ]);

        return $this->jsonDecode($response);
    }

    /**
     * @param string $patientId
     * @param string $siteId
     * @param string $practitionerId
     * @param Carbon $from
     * @param Carbon $to
     * @param string $note
     *
     * @return mixed
     */
    public function createAppointment($patientId, $siteId, $practitionerId, Carbon $from, Carbon $to, $note = '')
    {
        $visitTypes = $this->getVisitTypes();

        $data = [
            "event_type" => "appointment",
            'start_date' => $from->toDateTimeLocalString(),
            'end_date' => $to->toDateTimeLocalString(),
            'site_id' => $siteId,
            'site' => ['id' => $siteId],
            'patient_id' => $patientId,
            'patient' => ['id' => $patientId],
            'providers' => [
                ['id' => $practitionerId]
            ],
            'visit_date' => $from->toDateTimeLocalString(),
            'duration' => [
                'value' => $from->diffInMinutes($to),
                'units' => 'minutes'
            ],
            'visit_status' => 'scheduled',
            'notes' => $note,
            'type' => array_get(array_first($visitTypes), 'value')
        ];

        $response = $this->client->request('POST', 'api/v1/visits', [
            'headers' => [
                'X-CSRF-Token' => $this->csrfToken
            ],
            'json' => $data
        ]);

        return $this->jsonDecode($response);
    }

    /**
     * @param $availabilityId
     *
     * @return mixed
     */
    public function deleteAvailability($availabilityId)
    {
        $response = $this->client->request('DELETE', "api/v1/availability/{$availabilityId}", [
            'headers' => [
                'X-CSRF-Token' => $this->csrfToken
            ],
        ]);

        return $this->jsonDecode($response);
    }

    /**
     * @param $appointmentId
     *
     * @return mixed
     */
    public function deleteAppointment($appointmentId)
    {
        $data = [
            'visit_status' => 'cancelled'
        ];

        $response = $this->client->request('PATCH', "api/v1/visits/{$appointmentId}", [
            'headers' => [
                'X-CSRF-Token' => $this->csrfToken
            ],
            'json' => $data
        ]);

        return $this->jsonDecode($response);
    }

    /**
     * @return array
     */
    public function getVisitTypes()
    {
        return [
            ['value' => 'PSI60', 'name' => 'Psychiatry Intake 60'],
            ['value' => 'PSR45', 'name' => 'Psycotherapy - 45 min'],
            ['value' => 'FTWOP', 'name' => 'Family Therapy w/o patient'],
            ['value' => 'FTWP', 'name' => 'Family Therapy w/patient'],
            ['value' => 'PSR30', 'name' => 'Psycotherapy - 30 min'],
            ['value' => 'PSR60', 'name' => 'Psycotherapy - 60 min'],
        ];
    }

    /**
     * Get the result of Response.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return mixed
     */
    protected function jsonDecode(ResponseInterface $response)
    {
        return \GuzzleHttp\json_decode((string) $response->getBody(), true);
    }

    /**
     * @param $response
     * @return bool
     */
    public function is2FACheckTriggered($response)
    {
        $crawler = new Crawler($response->getBody()->getContents());
        $mfaForm = $crawler->filterXPath('//form[@id="mfa-form"]');
        return ($mfaForm->count() > 0);
    }

    /**
     * @param $response
     * @return bool
     */
    public function bypass2FA($loginStartTS, $mfaUserID)
    {
        $expirationTime = 60 * 1000;
        $currentTS = $loginStartTS;
        $verificationCode = null;
        while ($currentTS < ($loginStartTS + (2 * $expirationTime))) {
            sleep(10);
            $verificationCode = $this->getVerificationCode($loginStartTS);
           
            if ($verificationCode !== null) {
                break;
            }
            $currentTS = (new \DateTime("now", new \DateTimeZone('UTC')))->getTimestamp();
        }
        if ($verificationCode === null) {
            return false;  // email check task has been expired
        }
        $response = $this->client->request('POST', 'verify/confirm_mfa_on_user', [
            'form_params' => [
                'user_id' => $mfaUserID,
                'code' => $verificationCode,
            ],
            'on_stats' => function (TransferStats $stats) use (&$responseEffectiveURL) {
                $responseEffectiveURL = $stats->getEffectiveUri();
            },
        ]);
        $responseAsJSON = $this->jsonDecode($response);
        // success mfa response: "{"status":"approved","success":true}"
        // failed mfa: "{"status":"pending","success":true}"
        return $responseAsJSON["status"] === "approved";
    }

    protected function getVerificationCode($loginStartTS)
    {
        $userId = "me";
        $loginTimestamp = $loginStartTS;
        $thresholdTSInterval = 120;
        $gmail = new \Google_Service_Gmail((new GmailInboxService())->getClient());
        $messagesResponse = $gmail->users_messages->listUsersMessages($userId, ['maxResults' => 20]);
        try{
            $messages = $messagesResponse->getMessages();
            if ($messages != null) {
                foreach ($messages as $messagePreview) {
                    $message = $gmail->users_messages->get('me', $messagePreview->id, ["format" => "full"]);
                    $payload = $message->getPayload();
                    $headers = $payload->getHeaders();
                    $from = $this->getHeaderValue($headers, "From");
                    $subject = $this->getHeaderValue($headers, "Subject");
                    $messageDateTime = \DateTime::createFromFormat("D, d M Y H:i:s O T", $this->getHeaderValue($headers, "Date"));
                    $messageTimestamp = $messageDateTime->getTimestamp();
                    if ($messageTimestamp < ($loginTimestamp - $thresholdTSInterval)) {
                        return null;
                    }
                    if ((\strpos($from, "tridiuum.com") !== false) && (\strpos($subject, "verification code") !== false)) {
                        $body = $this->getEmailBody($payload);
                        if ($body !== null) {
                            $verificationCodeMatches = [];
                            preg_match('/^.*verification\scode\sis:\s\*([0-9]+)\*?.*$/m', $body, $verificationCodeMatches);
                            if (count($verificationCodeMatches) > 1) {
                                return $verificationCodeMatches[1];
                            }
                        }
                    }
                }

            }
            return null;
        } catch (\Exception $e) {
            \App\Helpers\SentryLogger::tridiuumCaptureException($e);
            return null;
        }
    }

    protected function getHeaderValue($headers, $headerName) {
        $matchedHeaders = array_filter($headers, function ($h) use ($headerName) {
            return $h->getName() == $headerName;
        });
        $firstMatchedHeader = array_shift($matchedHeaders);
        return $firstMatchedHeader->getValue();
    }

    protected function decodeEmailBody($body) {
        $rawData = $body;
        $sanitizedData = strtr($rawData,'-_', '+/');
        $decodedMessage = base64_decode($sanitizedData);
        if(!$decodedMessage){
            $decodedMessage = null;
        }
        return $decodedMessage;
    }

    protected function getEmailBody($payload) {
        $rawBodyData = $payload->getBody()->getData();
        if ($rawBodyData === null) {
            $parts = $payload->getParts();
            foreach ($parts as $part) {
                if ($part->getBody() !== null) {
                    $body = $part->getBody()->getData();
                    return $this->decodeEmailBody($body);
                }
            }
        } else {
            return $this->decodeEmailBody($rawBodyData);
        }
        return null;
    }
}
