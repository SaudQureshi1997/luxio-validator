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
}
