<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Support\Facades\App;
use Illuminate\Contracts\Container\Container;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use App\Services\Core\JsonResponseService;

// EXCEPTIONS
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var string[]
     */
    protected $dontReport = [];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var string[]
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Json response service
     *
     * @var JsonResponseService
     */
    protected $jsonResponseService;

    /**
     * Create a new exception handler instance.
     *
     * @param Container $container
     * @param JsonResponseService $jsonResponseService
     */
    public function __construct(Container $container, JsonResponseService $jsonResponseService)
    {
        parent::__construct($container);
        $this->jsonResponseService = $jsonResponseService;
    }

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            // Implement logging or reporting logic here if needed
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Throwable $exception
     * @return \Illuminate\Http\JsonResponse
     */
    public function render($request, Throwable $exception)
    {
        if ($request->is('api/*')) {
            $statusCode = $this->getStatusCodeFromException($exception);

            $responseData = [
                'message' => $this->getErrorMessage($exception),
            ];

            if (App::environment('local')) {
                $responseData = array_merge($responseData, [
                    'exception' => (new \ReflectionClass($exception))->getShortName(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'trace' => $exception->getTrace(),
                ]);
            }

            return $this->jsonResponseService->fail($responseData, $statusCode);
        }

        return parent::render($request, $exception);
    }

    /**
     * Get the HTTP status code based on the exception type.
     *
     * @param Throwable $exception
     * @return int
     */
    protected function getStatusCodeFromException(Throwable $exception): int
    {
        switch (true) {
            case $exception instanceof BadRequestException:
                return Response::HTTP_BAD_REQUEST;

            case $exception instanceof AuthenticationException:
                return Response::HTTP_UNAUTHORIZED;

            case $exception instanceof UnauthorizedException:
            case $exception instanceof AccessDeniedHttpException:
            case $exception instanceof AuthorizationException:
                return Response::HTTP_FORBIDDEN;

            case $exception instanceof ModelNotFoundException:
            case $exception instanceof NotFoundHttpException:
                return Response::HTTP_NOT_FOUND;

            case $exception instanceof MethodNotAllowedHttpException:
                return Response::HTTP_METHOD_NOT_ALLOWED;

            case $exception instanceof ValidationException:
                return Response::HTTP_UNPROCESSABLE_ENTITY;

            default:
                return Response::HTTP_INTERNAL_SERVER_ERROR;
        }
    }

    /**
     * Get the error message based on the exception type.
     *
     * @param Throwable $exception
     * @return array
     */
    protected function getErrorMessage(Throwable $exception): array
    {
        if ($exception instanceof ValidationException) {
            $errors = $exception->errors();
            $errorMessages = reset($errors)[0];
            return ['failed' => $errorMessages];
        }

        return [
            'failed' => json_decode($exception->getMessage(), true) ?: $exception->getMessage(),
        ];
    }
}