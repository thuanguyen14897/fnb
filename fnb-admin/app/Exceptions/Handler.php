<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
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

//    public function render($request, Throwable $exception)
//    {
//        $response = [
//            'message' => (string)$exception->getMessage(),
//            'status' => 400,
//        ];
//
//        if ($exception instanceof ValidationException) {
//            $response['message'] = $exception->errors();
//        }
//        if ($exception instanceof ErrorException) {
//            $response['message'] = $exception->errors();
//            $response['status'] = $exception->getStatusCode();
//        }
//        if ($exception instanceof ModelNotFoundException) {
//            $response['message'] = $exception->errors();
//            $response['status'] = $exception->getStatusCode();
//        }
//        if ($exception instanceof HttpException) {
//            $response['message'] = Response::$statusTexts[$exception->getStatusCode()];
//            $response['status'] = $exception->getStatusCode();
//        }
//
//
//        return response()->json([
//            'error' => $response,
//            'status' => $response['status'],
//        ], $response['status']);
//    }
}
