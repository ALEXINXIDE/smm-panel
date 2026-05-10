<?php

namespace App\Exceptions;

use Exception;

/**
 * Thrown for general order validation failures (e.g., service disabled, invalid link).
 * Handled by Handler.php to return a 422 Unprocessable Entity response.
 */
class OrderException extends Exception
{
    // Logic is centralized in Handler.php
}
