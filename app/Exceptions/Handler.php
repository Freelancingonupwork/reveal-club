<?php

namespace App\Exceptions;

use App\Traits\ApiResponser;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\InvalidArgumentException;
use Throwable;

class Handler extends ExceptionHandler
{
    use ApiResponser;
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

    public function render($request, Throwable $e): Response|JsonResponse|\Symfony\Component\HttpFoundation\Response
    {
        return $this->handleException($request, $e);
    }

    public function handleException($request, Throwable $exception): Response|JsonResponse|\Symfony\Component\HttpFoundation\Response
    {
        if ($exception instanceof MethodNotAllowedHttpException) {
            if ($request->is('api/*')) {
                return $this->errorResponse('The specified method for the request is invalid', [0 => [
                    'message' => 'Please try with other request type (POST, PUT, GET, DELETE).',
                    'fieldName' => 'API',
                    'errors' => $exception->getMessage(),
                ]], 405);
            }

            return response()->view('error', ['error' => $exception, 'status' => $exception->getStatusCode() ,'message' => 'The specified method for the request is invalid']);
        }

        if ($exception instanceof NotFoundHttpException) {
            if ($request->is('api/*')) {
                return $this->errorResponse('The specified URL cannot be found', [0 => [
                    'message' => 'The API endpoint is invalid.',
                    'fieldName' => 'endpoint',
                    'errors' => $exception->getMessage(),
                ]], 404);
            }

            return response()->view('error', ['error' => $exception, 'status' => $exception->getStatusCode(), 'message' => '']);
        }

        if ($exception instanceof HttpException) {
            if ($request->is('api/*')) {
                return $this->errorResponse('Unauthorized action', [0 => [
                    'message' => 'The authenticated user is not allowed to access the specified API endpoint.',
                    'fieldName' => 'role',
                    'errors' => $exception->getMessage(),
                ]], $exception->getStatusCode());
            }

            return response()->view('error', ['error' => $exception, 'status' => $exception->getStatusCode(), 'message' => 'The authenticated user is not allowed to access']);
        }

        if ($exception instanceof AuthenticationException) {
            if ($request->is('api/*')) {
                return $this->errorResponse('You are logged out from this system', [0 => [
                    'message' => 'Please re-login to the system.',
                    'fieldName' => 'token',
                    'errors' => $exception->getMessage(),
                ]], 401);
            }

            return response()->view('error', ['error' => $exception, 'status' => 401,  'message' => 'You are logged out from this system']);
        }

        if ($exception instanceof ModelNotFoundException) {
            $trim = explode('/', Request::getPathInfo());

            if ($request->is('api/*')) {
                return $this->errorResponse('No ' . $trim[3] . ' Data Found', [0 => [
                    'message' => 'The ' . $trim[3] . ' is not found',
                    'fieldName' => 'id',
                    'errors' => $exception->getMessage(),
                ]], 404);
            }

            return response()->view('error', ['error' => $exception, 'status' => 404, 'message' => ""]);
        }

        if ($exception instanceof ValidationException) {
            $error = [];

            if ($request->is('api/*')) {
                foreach ($exception->errors() as $key => $value) {
                    $error = array_merge($error, $value);
                }

                return $this->errorResponse('These fields are required', $error, (isset($error[0]['fieldName']) && $error[0]['fieldName'] == "email,password") ? 401 : $exception->status);
            }
        }

        if ($exception instanceof QueryException) {
            if ($request->is('api/*')) {
                return $this->errorResponse('Database Query Error', [0 => [
                    'message' => 'Invalid SQL format or Table field or Type',
                    'fieldName' => 'database',
                    'errors' => $exception->getMessage(),
                ]], 502);
            }

            return response()->view('error', ['error' => $exception, 'status' => 502, 'message' => "Invalid SQL format or Table field or Type"]);
        }

        if ($exception instanceof InvalidArgumentException) {
            if ($request->is('api/*')) {
                return $this->errorResponse('Error', $exception->getMessage(), $exception->getCode());
            }

            return response()->view('error', ['error' => $exception, 'status' => $exception->getCode(), 'message' => $exception->getMessage()]);
        }

        if ($exception instanceof AccessDeniedHttpException) {
            if ($request->is('api/*')) {
                return $this->errorResponse('Error', $exception->getMessage(), $exception->getCode());
            }

            return response()->view('error', ['error' => $exception, 'status' => $exception->getCode(), 'message' => $exception->getMessage()]);
        }

        // If the error comes from Turnstile, also use the default render
        if (
            config('app.debug') ||
            (is_object($exception) && (
                strpos(get_class($exception), 'Turnstile') !== false ||
                (get_class($exception) === 'Illuminate\Validation\ValidationException' && 
                strpos($exception->getMessage(), 'cf-turnstile-response') !== false)
            ))
        ) {
            return parent::render($request, $exception);
        }

        if ($request->is('api/*')) {
            return $this->errorResponse('Unexpected Exception. Try later', $exception->getTrace(), $exception->getCode());
        }
        
        return response()->view('error', ['error' => $exception, 'status' => $exception->getCode(), 'message' => $exception->getMessage()]);
    }
}
