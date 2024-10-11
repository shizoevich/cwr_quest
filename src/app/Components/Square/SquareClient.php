<?php

declare(strict_types=1);

namespace App\Components\Square;

use Square\Environment;
use Square\Server;
use Square\SquareClient as BaseSquareClient;

/**
 * Copied from Old Square SDK
 * Class SquareClient
 * @package App\Components\Square
 */
class SquareClient extends BaseSquareClient
{
    /**
     * A map of all baseurls used in different environments and servers
     *
     * @var array
     */
    const ENVIRONMENT_MAP = [
        Environment::PRODUCTION => [
            Server::DEFAULT_ => 'https://connect.squareup.com',
        ],
        Environment::SANDBOX => [
            Server::DEFAULT_ => 'https://connect.squareupsandbox.com',
        ],
    ];
    
    /**
     * Returns Transactions Api
     */
    public function getTransactionsApi(): \Square\Apis\TransactionsApi
    {
        return new TransactionsApi($this);
    }
}
