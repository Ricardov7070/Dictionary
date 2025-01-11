<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render ($request, Throwable $exception) {

        if ($exception instanceof NotFoundHttpException) {

            return response()->json([
                'error' => 'Route not found. Please check the URL and try again.'
            ], 400);

        }

        return parent::render($request, $exception);
        
    }


    protected function unauthenticated ($request, \Illuminate\Auth\AuthenticationException $exception) {
    
        if ($request->expectsJson() || $request->is('api/*')) {

            return response()->json([
                'message' => 'You must be authenticated to access this resource.',
            ], 401);

        }

        return parent::unauthenticated($request, $exception);

    }
    
}
