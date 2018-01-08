<?php

namespace DialInno\Jaal;

use Illuminate\Support\ServiceProvider;
use DialInno\Jaal\Contracts\Response as ResponseContract;
use DialInno\Jaal\Response;
use DialInno\Jaal\Contracts\Jaal as JaalContract;
use DialInno\Jaal\Jaal;

class JaalProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        // devs may typehint the response contract
        $this->app->singleton(ResponseContract::class, function () {
            return new Response();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [ResponseContract::class];
    }
}
