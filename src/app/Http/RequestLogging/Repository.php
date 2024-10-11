<?php

namespace App\Http\RequestLogging;

use App\Contracts\Http\RequestLogging\Repository as RepositoryContract;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Contracts\Http\RequestLogging\Store;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Repository implements RepositoryContract
{

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var \app\Contracts\Http\RequestLogging\store
     */
    protected $store;


    /**
     * @var float
     */
    protected $startTime;

    /**
     * Create a new RequestLogging repository instance.
     *
     * @param  \App\Contracts\Http\RequestLogging\store $store
     */
    public function __construct(Store $store)
    {
        $this->store = $store;
        $this->startTime = isset($_SERVER['REQUEST_TIME_FLOAT']) ? $_SERVER['REQUEST_TIME_FLOAT'] : microtime(true);
    }


    /**
     *
     * @param Response $response
     * @return mixed
     */
    public function putResponse($response)
    {
        try {
            $this->put('response_data', $response->getOriginalContent());
            $this->put('status_code', $response->getStatusCode());
            $this->put('response_headers', $response->headers->allPreserveCase());
            $this->put('user_id', $this->getUserId());
            $this->put('start_time', $this->startTime);
            $endTime = microtime(true);
            $this->put('end_time', $endTime);
            $this->put('duration', $endTime - $this->startTime);
            $this->put('date', class_exists('MongoDate') ? new \MongoDate() :  date('m/d/Y h:i:s a', time()));
        } catch (\Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            \App\Helpers\SentryLogger::captureException($e);
        }
    }

    /**
     *
     * @param array $logs
     * @return mixed
     */
    public function putSmartFox($logs) {
        try {
            $this->put('smartfox_logs', $logs);
        } catch (\Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            \App\Helpers\SentryLogger::captureException($e);
        }
    }

    /**
     *
     * @param Request $request
     * @return mixed
     */
    public function putRequest($request)
    {
        try {
            $this->put('client_ip', $request->getClientIp());
            $this->put('type', $request->getMethod());
            $this->put('user_agent', $request->header('USER_AGENT'));
            $this->put('authorization_header', $request->header('Authorization'));
            $this->put('url', $request->url());
            $requestParams['get'] = $_GET;
            $requestParams['post'] = $_POST;
            $requestParams['file'] = $_FILES;
            $requestParams['raw'] = $request->getContent();
            $this->put('request_params', $requestParams);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            \App\Helpers\SentryLogger::captureException($e);
        }
    }

    /**
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public function put($key, $value)
    {
        $this->data = array_add($this->data, $key, $value);
    }

    /**
     *
     * @return bool
     */
    public function save()
    {
        try {
            return $this->store->save($this->data);
        } catch (\Exception $e) {
            Log::error($e->getMessage(), ['context' => 'RequestLogging unable to work']);
            \App\Helpers\SentryLogger::captureException($e);
        }
    }

    /**
     * @return null|int
     */
    protected function getUserId()
    {
        $userId = null;
        if (Auth::check()) {
            $userId = Auth::user()->id;
        }

        return $userId;
    }

}