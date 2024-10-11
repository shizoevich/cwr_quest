<?php

namespace App\MonologHandlers;

use DB;
use Illuminate\Support\Facades\Auth;
use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;

class MysqlHandler extends AbstractProcessingHandler
{
    protected $table;
    protected $connection;

    public function __construct($level = Logger::DEBUG, $bubble = true)
    {
        $this->table = config('parser.tridiuum.log_table');

        parent::__construct($level, $bubble);
    }

    protected function write(array $record)
    {
        $data = json_decode($record['message'], true);
        if(empty($data)) {
            return;
        }
        $log = [
            'url'          => $data['url'],
            'method'       => $data['method'],
            'request_body' => $data['request_body'],
            'status_code'  => $data['status_code'] ?? 0,
        ];

        DB::connection('mysql')->table($this->table)->insert($log);
    }
}