<?php

namespace App\Exceptions;

use Exception;

/**
 * Thrown when a user attempts to place an order without enough balance.
 * Handled by Handler.php to return a 402 Payment Required response.
 */
class InsufficientFundsException extends Exception
{
    // Logic is centralized in Handler.php
}
