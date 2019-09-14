<?php

namespace elphis\Providers;

use elphis\Providers\ServiceProvider;
use elphis\Utils\Validator;

class ValidationServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Validator::class, function () {
            return new Validator();
        });
    }
}
