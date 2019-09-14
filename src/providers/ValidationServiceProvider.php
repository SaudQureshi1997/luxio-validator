<?php

namespace Elphis\Providers;

use Elphis\Providers\ServiceProvider;
use Elphis\Utils\Validator;

class ValidationServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Validator::class, function () {
            return new Validator();
        });
    }
}
