<?php

namespace App\Console\Commands\Parsers;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Psr7\Request;
use Symfony\Component\DomCrawler\Crawler;
use App\Helpers\Sites\OfficeAlly\OfficeAlly;

class TestOfficeAllyLogin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oa:test-login';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $login = 'groupbwt';
        $password = ''; // OA account password
        $command = 'node ' . base_path() . '/puppeteer-scripts/' . config('officeally.login_script') . ' ' . $login . ' ' . $password;
        $output = [];
        $returnValue = null;
        exec($command, $output, $returnValue);

        if ($returnValue !== 0 || !count($output)) {
            return;
        }

        $oldCookies = json_decode($output[0], true);
        $newCookies = [];
        foreach ($oldCookies as $item) {
            $newCookies[$item['name']] = $item['value'];
        }

        var_dump($newCookies);

        // --------------------------------------------

        $client = $this->getClient($newCookies);

        $request = new Request('GET', 'Default.aspx');
        $response = $client->send($request, []);

        if (!$this->isResponseAuthenticated($response)) {
            return;
        }

        $request = new Request('GET', 'ManagePatients/EditPatient.aspx?Tab=P&PageAction=edit&PID=63850291');
        $response = $client->send($request, []);

        if (!$this->isResponseAuthenticated($response)) {
            return;
        }

        $pageContent = $response->getBody()->getContents();
        $crawler = new Crawler($pageContent);
        $patient = $this->parsePatient($crawler);

        var_dump('PARSED PATIENT: ' . json_encode($patient));
        
        // $testParser = new OfficeAlly('groupbwt');
        // var_dump($testParser->loginWithPuppeteer());
    }

    private function getClient($cookiesData)
    {
        $cookies = CookieJar::fromArray($cookiesData, 'pm.officeally.com');
        $clientConfig = [
            'cookies'         => $cookies ?? true,
            'verify'          => false,
            'base_uri'        => 'https://pm.officeally.com/pm/',
            'allow_redirects' => false,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.81 Safari/537.36'
            ]
        ];

        if (config('officeally.proxy_enabled')) {
            $clientConfig['proxy'] = config('officeally.proxy');
        }
        
        return new Client($clientConfig);
    }

    private function parsePatient(Crawler $crawler)
    {
        try {
            $patientId = $crawler->filter('#ctl00_phFolderContent_ucPatient_lblPatientID')->first()->text();
        } catch(\InvalidArgumentException $e) {
            return null;
        }

        if (!$patientId) {
            return null;
        }

        return [
            'first_name'           => $crawler->filter('#ctl00_phFolderContent_ucPatient_lblFirstName')->first()->text(),
            'last_name'            => $crawler->filter('#ctl00_phFolderContent_ucPatient_lblLastName')->first()->text(),
            'middle_initial'       => $crawler->filter('#ctl00_phFolderContent_ucPatient_lblMiddleName')->first()->text(),
            'sex'                  => $crawler->filter('#ctl00_phFolderContent_ucPatient_lblGender')->first()->text(),
            'subscriber_id'        => $crawler->filter('input#ctl00_phFolderContent_ucPatient_InsuranceSubscriberID')->first()->attr('value'),
            'primary_insurance'    => $crawler->filter('input#ctl00_phFolderContent_ucPatient_InsuranceName')->first()->attr('value'),
            'visit_copay'          => $crawler->filter('input#ctl00_phFolderContent_ucPatient_InsuranceVisitCopay')->first()->attr('value') ?? 0,
            'visits_auth'          => intval($crawler->filter('input#ctl00_phFolderContent_ucPatient_Authorization_NumberOfVisitsAuthorized')->first()->attr('value')),
            'visits_auth_left'     => intval($crawler->filter('input#ctl00_phFolderContent_ucPatient_Authorization_NumberOfVisitsLeft')->first()->attr('value')),
            'city' => $crawler->filter('input#ctl00_phFolderContent_ucPatient_City')->first()->attr('value'),
            'state' => $crawler->filter('#ctl00_phFolderContent_ucPatient_lstState option:selected')->first()->attr('value'),
            'address' => $crawler->filter('input#ctl00_phFolderContent_ucPatient_AddressLine1')->first()->attr('value'),
            'address_2' => $crawler->filter('input#ctl00_phFolderContent_ucPatient_AddressLine2')->first()->attr('value'),
            'zip' => $crawler->filter('input#ctl00_phFolderContent_ucPatient_Zip')->first()->attr('value'),
        ];
    }

    private function isResponseAuthenticated($response): bool
    {
        $locationHeader = data_get($response->getHeader('Location'), '0');
        if(!$locationHeader) {
            return true;
        }
        
        return !str_contains($locationHeader, 'Login.aspx');
    }
}
