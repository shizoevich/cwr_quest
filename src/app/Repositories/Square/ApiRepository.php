<?php

namespace App\Repositories\Square;

use App\Models\Square\SquareLog;
use App\Repositories\Square\Traits\Customer;
use App\Repositories\Square\Traits\CustomerCard;
use App\Repositories\Square\Traits\General;
use App\Repositories\Square\Traits\Invoice;
use App\Repositories\Square\Traits\Order;
use App\Repositories\Square\Traits\Payment;
use Auth;
use RuntimeException;
use Square\Environment;
use Square\SquareClient;
use App\Components\Square\SquareClient as CustomSquareClient;

/**
 * Implementation for API version 2020-11-18
 * Class ApiRepository
 * @package App\Repositories\Square
 */
class ApiRepository implements ApiRepositoryInterface
{
    use Customer, Payment, CustomerCard, General, Invoice, Order;
    
    /** @var SquareClient */
    private $client;
    
    /**
     * @return string
     */
    private function getEnvironment()
    {
        switch (config('square.mode')) {
            case 'production':
                return Environment::PRODUCTION;
            case 'sandbox':
                return Environment::SANDBOX;
            default:
                throw new RuntimeException('Undefined Square environment. Available environments: ' . Environment::PRODUCTION . ', ' . Environment::SANDBOX);
        }
    }
    
    /**
     * @return SquareClient
     */
    public function getClient(): SquareClient
    {
        if (!isset($this->client)) {
            $this->client = new CustomSquareClient([
                'accessToken' => config('square.access_token'),
                'environment' => $this->getEnvironment(),
            ]);
        }
        
        return $this->client;
    }
    
    /**
     * @inheritDoc
     */
    public function writeLog(string $action, array $request, array $response, bool $isSuccess)
    {
        SquareLog::create([
            'user_id'    => Auth::check() ? Auth::id() : null,
            'action'     => $action,
            'request'    => $request,
            'response'   => $response,
            'is_success' => $isSuccess,
        ]);
    }
    
    /**
     * @inheritDoc
     */
    public function writeErrorLog(string $action, array $request, array $response)
    {
        $this->writeLog($action, $request, $response, false);
    }
    
    /**
     * @inheritDoc
     */
    public function writeSuccessLog(string $action, array $request, array $response)
    {
        $this->writeLog($action, $request, $response, true);
    }
}