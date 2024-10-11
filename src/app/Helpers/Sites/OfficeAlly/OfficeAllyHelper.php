<?php

namespace App\Helpers\Sites\OfficeAlly;

use App\Helpers\Sites\OfficeAlly\Traits\Appointments;
use App\Helpers\Sites\OfficeAlly\Traits\Insurances;
use App\Helpers\Sites\OfficeAlly\Traits\Offices;
use App\Helpers\Sites\OfficeAlly\Traits\Patients;
use App\Helpers\Sites\OfficeAlly\Traits\Payments;
use App\Helpers\Sites\OfficeAlly\Traits\Providers;
use App\Helpers\Sites\OfficeAlly\Traits\Visits;
use App\Models\Officeally\OfficeallyLog;
use Symfony\Component\DomCrawler\Crawler;

class OfficeAllyHelper
{
    use Visits, Appointments, Patients, Providers, Payments, Offices, Insurances;
    
    /** @var OfficeAlly */
    private $officeAlly;
    
    public function __construct(string $accountName)
    {
        $this->officeAlly = new OfficeAlly($accountName);
    }
    
    /**
     * @return string|null
     */
    public function getRequestVerificationToken()
    {
        $page = $this->officeAlly->get('Appointments/ViewAppointments.aspx', [], true)->getBody()->getContents();
        $crawler = new Crawler($page);
        
        return $crawler->filter('[name="__RequestVerificationToken"]')->first()->attr('value');
    }
    
    /**
     * @param int  $action
     * @param bool $isSuccess
     * @param null $data
     * @param null $message
     *
     * @return OfficeallyLog|null
     */
    protected function log(int $action, bool $isSuccess, $data = null, $message = null)
    {
        if(config('officeally.log_to_table_enabled')) {
            return OfficeallyLog::create([
                'action' => $action,
                'is_success' => $isSuccess,
                'message' => $message,
                'data' => $data,
            ]);
        }
        
        return null;
    }
    
    /**
     * @return bool
     */
    protected function isProduction(): bool
    {
        return config('officeally.env') === 'production';
    }
    
    /**
     * @param $value
     *
     * @return int
     */
    protected function prepareValue($value)
    {
        if($value === 'true') {
            return 1;
        } else if($value === 'false') {
            return 0;
        }
        
        return $value;
    }

    protected function removeSpaces($value)
    {
        return trim(preg_replace('/\s+/', ' ', $value));
    }
}