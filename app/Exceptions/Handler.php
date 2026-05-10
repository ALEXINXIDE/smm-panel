<?php

namespace App\Exceptions;

use App\Exceptions\InsufficientFundsException;
use App\Exceptions\OrderException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

// ============================================================================
// Handler.php — Centralized Exception Handling
// ============================================================================
// Improvements over original (which just extended the base handler):
//  1. Domain exceptions map to HTTP responses cleanly
//  2. Structured JSON error format for API consumers
//  3. Sentry integration for production error tracking
//  4. Request context attached to every exception log
//  5. Security: stack traces never exposed in production
//  6. Payment/order exceptions logged to separate channel
// ============================================================================

class Handler extends ExceptionHandler
{
    /**
     * Exception types that are never reported to Sentry or logs.
     * These are expected, user-triggered events — not bugs.
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
        \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
        InsufficientFundsException::class,  // Expected business flow
        OrderException::class,               // Expected business flow
    ];

    /**
     * Fields that should never appear in exception reports.
     * SECURITY: Prevents accidental password logging.
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
        'card_number',
        'cvv',
        'stripe_token',
    ];

    /**
     * Register all exception handlers.
     */
    public function register(): void
    {
        // ── Sentry Integration ────────────────────────────────────────────
        $this->reportable(function (Throwable $e) {
            if (app()->bound('sentry') && $this->shouldReport($e)) {
                // Attach user context for better debugging in Sentry
                if ($user = auth()->user()) {
                    \Sentry\configureScope(function (\Sentry\State\Scope $scope) use ($user) {
                        $scope->setUser([
                            'id'    => $user->id,
                            'email' => $user->email,
                        ]);
                    });
                }
                app('sentry')->captureException($e);
            }
        });

        // ── Domain Exception Rendering ────────────────────────────────────
        $this->renderable(function (InsufficientFundsException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 402);
            }
            return back()->withErrors(['funds' => $e->getMessage()]);
        });

        $this->renderable(function (OrderException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
            return back()->withErrors(['order' => $e->getMessage()]);
        });

        $this->renderable(function (PaymentVerificationException $e, Request $request) {
            \Illuminate\Support\Facades\Log::channel('payments')
                ->warning('Payment verification failed', [
                    'error'      => $e->getMessage(),
                    'ip'         => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            return response()->json(['message' => 'Payment verification failed.'], 400);
        });

        // ── 404 Handling ──────────────────────────────────────────────────
        $this->renderable(function (NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Resource not found.'], 404);
            }
            // Return 404 view if it exists, otherwise default
            if (view()->exists('errors.404')) {
                return response()->view('errors.404', [], 404);
            }
        });
    }

    /**
     * Render an exception into an HTTP response.
     * In production, never expose internal details.
     */
    public function render($request, Throwable $e)
    {
        // Log request context for debugging (sanitized)
        if ($this->shouldReport($e)) {
            \Illuminate\Support\Facades\Log::error($e->getMessage(), [
                'exception'  => get_class($e),
                'file'       => $e->getFile(),
                'line'       => $e->getLine(),
                'url'        => $request->fullUrl(),
                'method'     => $request->method(),
                'user_id'    => auth()->id(),
                'ip'         => $request->ip(),
            ]);
        }

        return parent::render($request, $e);
    }
}
