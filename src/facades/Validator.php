<?php

namespace elphis\Support\Facades;

use elphis\Support\Facades\Facade;
use elphis\Utils\Validator as CoreValidator;

class Validator extends Facade
{
    public static function getFacadeAccessor()
    {
        return CoreValidator::class;
    }
}
