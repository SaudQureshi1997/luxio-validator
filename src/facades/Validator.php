<?php

namespace Elphis\Support\Facades;

use Elphis\Support\Facades\Facade;
use Elphis\Utils\Validator as CoreValidator;

class Validator extends Facade
{
    public static function getFacadeAccessor()
    {
        return CoreValidator::class;
    }
}
