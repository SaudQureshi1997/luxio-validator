<?php

namespace Luxio\Utils\Providers;

use Luxio\Providers\ServiceProvider;
use Luxio\Utils\Validator;

class ValidationServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Validator::class, function () {
            return new Validator();
        });
    }

    public function boot()
    {
        $validator = $this->app->resolve(Validator::class);

        $validator->extends('digits', function ($number, $params) {
            return strlen($number) === (int) $params[0];
        }, ':attribute must be of length :value');
    }
}
