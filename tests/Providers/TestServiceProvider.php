<?php

namespace DialInno\Jaal\Tests\Providers;

use Illuminate\Support\ServiceProvider;
use DialInno\Jaal\Tests\Api\JsonApiV1;

class TestServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(JsonApiV1::class, function ($app) {
            return new JsonApiV1();
        });
    }
}
