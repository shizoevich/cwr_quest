<?php

namespace App\Http\Middleware;

use Closure;
use \App\Contracts\Http\RequestLogging\Repository as RequestLogging;
use \Illuminate\Http\Request;

class RequestLoggingMiddleware
{
    /**
     * @var \App\Contracts\Http\RequestLogging\Repository
     */
    private $requestLogging;

    /**
     * RequestLoggingMiddleware constructor.
     * @param RequestLogging $requestLogging
     */
    function __construct(RequestLogging $requestLogging)
    {
        $this->requestLogging = $requestLogging;
    }


    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }

    /**
     * @param  $request
     * @param  $response
     */
    public function terminate($request, $response)
    {
        try {
            if($this->needsWriteLog($request, $response)) {
                $this->requestLogging->putRequest($request);
                $this->requestLogging->putResponse($response);
                $this->requestLogging->save();
            }
        } catch(\Throwable $e) {
            \App\Helpers\SentryLogger::captureException($e);
            \Log::error($e->getTraceAsString());
        }
        
    }
    
    /**
     * @param $request
     * @param $response
     *
     * @return bool
     */
    protected function needsWriteLog(Request $request, $response)
    {
        $allowedRequestMethods = ['POST', 'PUT', 'PATCH', 'DELETE'];
        
        return $response->getStatusCode() >= 500 ||
            (in_array($request->getMethod(), $allowedRequestMethods) && !$request->route()->named('documents.thumbnail')) ||
            $request->route()->named('appointment-dates') ||
            $request->route()->named('pn.previous-data') ||
            $request->route()->named('complete-appointment-data');
    }
}
