<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
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

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $exception)
    {
        // Handle API requests
        if ($request->expectsJson()) {
            return $this->handleApiException($exception);
        }
        
        // Handle web requests with custom error pages
        return $this->handleWebException($request, $exception);
    }
    
    /**
     * Handle API exceptions
     */
    private function handleApiException(Throwable $exception)
    {
        $statusCode = $this->getStatusCode($exception);
        $message = $this->getUserFriendlyMessage($exception);
        
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $statusCode);
    }
    
    /**
     * Handle web exceptions - Route to custom error pages
     */
    private function handleWebException($request, Throwable $exception)
    {
        $statusCode = $this->getStatusCode($exception);
        
        // Map specific exceptions to custom error views
        switch (true) {
            case $exception instanceof NotFoundHttpException:
                return $this->renderErrorPage('errors.404', 'Page Not Found', $statusCode);
                
            case $exception instanceof AuthenticationException:
                return redirect()->route('login')->with('error', $this->getUserFriendlyMessage($exception));
                
            case $exception instanceof HttpException && $statusCode === 403:
                return $this->renderErrorPage('errors.403', 'Access Denied', $statusCode);
                
            case $exception instanceof HttpException && $statusCode === 419:
                return $this->renderErrorPage('errors.419', 'Session Expired', $statusCode);
                
            case $exception instanceof ValidationException:
                // Let Laravel handle validation exceptions normally
                return parent::render($request, $exception);
                
            default:
                return $this->renderErrorPage('errors.generic', 'Something Went Wrong', $statusCode);
        }
    }
    
    /**
     * Render a custom error page
     */
    private function renderErrorPage(string $view, string $title, int $statusCode)
    {
        return response()->view($view, [
            'title' => $title,
            'statusCode' => $statusCode,
            'message' => $this->getUserFriendlyMessageBasedOnCode($statusCode),
        ], $statusCode);
    }
    
    /**
     * Get HTTP status code for exception
     */
    private function getStatusCode(Throwable $exception): int
    {
        if ($exception instanceof HttpException) {
            return $exception->getStatusCode();
        }
        
        if ($exception instanceof ModelNotFoundException) {
            return 404;
        }
        
        if ($exception instanceof AuthenticationException) {
            return 401;
        }
        
        if ($exception instanceof ValidationException) {
            return 422;
        }
        
        if ($exception instanceof MethodNotAllowedHttpException) {
            return 405;
        }
        
        if ($exception instanceof ThrottleRequestsException) {
            return 429;
        }
        
        return 500;
    }
    
    /**
     * Get user-friendly error message (SCP-109: Generic messages)
     */
    private function getUserFriendlyMessage(Throwable $exception): string
    {
        if ($exception instanceof NotFoundHttpException) {
            return 'The requested page could not be found.';
        }
        
        if ($exception instanceof ModelNotFoundException) {
            return 'The requested resource could not be found.';
        }
        
        if ($exception instanceof AuthenticationException) {
            return 'You must be logged in to access this page.';
        }
        
        if ($exception instanceof ValidationException) {
            return 'There was an error with your submission. Please check your input.';
        }
        
        if ($exception instanceof MethodNotAllowedHttpException) {
            return 'This method is not allowed for the requested resource.';
        }
        
        if ($exception instanceof ThrottleRequestsException) {
            return 'Too many requests. Please try again later.';
        }
        
        // Default generic message (never show actual error to users)
        return 'An unexpected error occurred. Please try again later.';
    }
    
    /**
     * Get message based on HTTP status code
     */
    private function getUserFriendlyMessageBasedOnCode(int $statusCode): string
    {
        $messages = [
            400 => 'Bad Request: The server could not understand your request.',
            401 => 'Unauthorized: Please log in to access this page.',
            403 => 'Access Denied: You do not have permission to access this page.',
            404 => 'Page Not Found: The requested page could not be found.',
            405 => 'Method Not Allowed: This action is not permitted.',
            419 => 'Session Expired: Your session has expired. Please refresh the page.',
            422 => 'Validation Error: Please check your input and try again.',
            429 => 'Too Many Requests: Please wait before trying again.',
            500 => 'Server Error: An unexpected error occurred. Please try again later.',
            503 => 'Service Unavailable: The server is temporarily unavailable.',
        ];
        
        return $messages[$statusCode] ?? 'An unexpected error occurred. Please try again later.';
    }
}