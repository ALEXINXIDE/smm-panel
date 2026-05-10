<?php

namespace App\Exceptions;

use Exception;

/**
 * Thrown when an external payment (e.g., Stripe, PayPal) fails verification.
 * Handled by Handler.php to log to the 'payments' channel and return a 400 response.
 */
class PaymentVerificationException extends Exception
{
    // Logic is centralized in Handler.php
}
