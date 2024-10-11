<?php

namespace App\Http\RequestLogging;

use App\Contracts\Http\RequestLogging\Store;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\Log;

class MongoStore implements Store
{

    /**
     * The database connection instance.
     *
     * @var \Illuminate\Database\ConnectionInterface
     */
    protected $connection;

    /**
     *  The name of the RequestLogging table.
     *
     * @var string
     */
    protected $table;


    function __construct(ConnectionInterface $connection = null, $table)
    {
        $this->connection = $connection;
    }

    /**
     * Get a query builder for the RequestLogging table.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function table()
    {
        return $this->connection->table($this->table);
    }


    /**
     * @param array $data
     * @return bool
     */
    public function save(array $data)
    {
        $return = false;
        try {
            if ($this->connection instanceof ConnectionInterface) {
                $response = $this->connection->getMongoDB()->selectCollection('log')->insert($data);
                if (isset($response['ok']) && $response['ok'] == 1) {
                    $return = true;
                }
            }
        } catch (\Exception  $e) {
            Log::error($e->getMessage());
            \App\Helpers\SentryLogger::captureException($e);
        }

        return $return;
    }
}