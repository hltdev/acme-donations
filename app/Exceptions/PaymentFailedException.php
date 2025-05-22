<?php

namespace App\Exceptions;

use Exception;

class PaymentFailedException extends Exception
{
    public function __construct(string $message = 'Payment could not be processed')
    {
        parent::__construct($message);
    }
}
