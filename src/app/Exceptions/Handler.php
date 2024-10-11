<?php

namespace App\Exceptions;

use Exception;
use App\Exceptions\Email\EmailInRejectListException;
use App\Exceptions\Email\EmailNotSentException;
use App\Exceptions\Officeally\OfficeallyException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        if ($this->shouldReport($exception) && app()->bound('sentry')) {
            app('sentry')->captureException($exception);
        }
        
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if ($exception instanceof OfficeallyException && $request->expectsJson()) {
            return response()->json([
                'error' => $exception->getHumanReadableMessage()
            ], $exception->getStatusCode());
        }
        if ($exception instanceof EmailNotSentException && $request->expectsJson()) {
            return response()->json([
                'error' => [
                    'message' => $exception->getMessage(),
                    'exception_type' => class_basename(EmailNotSentException::class),
                ]
            ], 409);
        }
        if ($exception instanceof EmailInRejectListException && $request->expectsJson()) {
            return response()->json([
                'error' => [
                    'message' => $exception->getMessage(),
                    'email' => $exception->getEmail(),
                    'exception_type' => class_basename(EmailInRejectListException::class),
                ]
            ], 409);
        }
        if ($exception instanceof PhoneIsUnableToReceiveSmsException && $request->expectsJson()) {
            return response()->json([
                'error' => [
                    'message' => $exception->getMessage(),
                    'exception_type' => class_basename(PhoneIsUnableToReceiveSmsException::class),
                ]
            ], 409);
        }

        return parent::render($request, $exception);
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest(route('login'));
    }
}
