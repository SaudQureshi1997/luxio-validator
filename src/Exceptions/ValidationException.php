<?php

namespace Elphis\Exceptions;

use Exception;

class ValidationException extends Exception
{
    protected $code = 422;

    public function __construct(array $messages)
    {
        parent::__construct(json_encode($messages), $this->code);
    }
}
