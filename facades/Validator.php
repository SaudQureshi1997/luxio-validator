<?php

namespace Luxio\Support\Facades;

use Luxio\Support\Facades\Facade;
use Luxio\Utils\Validator as CoreValidator;

class Validator extends Facade
{
    public static function getFacadeAccessor()
    {
        return CoreValidator::class;
    }
}
