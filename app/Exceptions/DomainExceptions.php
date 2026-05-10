<?php

namespace App\Exceptions;

// ============================================================================
// Domain Exceptions — Clean error handling without string parsing
// ============================================================================
// WHY CUSTOM EXCEPTIONS:
//  Controllers can catch specific exception types and return appropriate
//  HTTP responses. This is cleaner than checking error message strings.
//
//  Usage in controller:
//    try {
//        $order = $this->orderService->createOrder($data);
//    } catch (InsufficientFundsException $e) {
//        return back()->withErrors(['funds' => $e->getMessage()]);
//    } catch (OrderException $e) {
//        return back()->withErrors(['order' => $e->getMessage()]);
//    }
// ============================================================================

/**
 * Base exception for all order-related business rule violations.
 * These are user-facing errors (400-class), not server errors.
 */
class OrderException extends \RuntimeException
{
    public function __construct(string $message, int $code = 422, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

/**
 * Thrown when a user attempts to place an order with insufficient funds.
 * Separate class so controllers can show a "add funds" prompt.
 */
class InsufficientFundsException extends OrderException
{
    public function __construct(string $message = 'Insufficient account balance.')
    {
        parent::__construct($message, 402);
    }
}

/**
 * Thrown when a payment verification fails.
 * Separate from OrderException so the handler can log payment fraud attempts.
 */
class PaymentVerificationException extends \RuntimeException
{
    public function __construct(string $message = 'Payment verification failed.', int $code = 400)
    {
        parent::__construct($message, $code);
    }
}

/**
 * Thrown when a provider API call fails after all retries.
 */
class ProviderApiException extends \RuntimeException
{
    public function __construct(
        string $message,
        private readonly string $providerName = '',
        private readonly string $action = ''
    ) {
        parent::__construct($message);
    }

    public function getProviderName(): string { return $this->providerName; }
    public function getAction(): string { return $this->action; }
}
