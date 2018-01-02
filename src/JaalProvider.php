<?php

namespace DialInno\Jaal;

use Illuminate\Support\ServiceProvider;

class JaalProvider extends ServiceProvider
{
    /**
     * Commands to register.
     *
     * @var commands
     */
    protected $commands = [

        \DialInno\Jaal\Commands\ApiMakeCommand::class

    ];
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        // $this->commands($this->commands);

        // $this->app->singleton(MyPackage::class, function () {
        //     return new MyPackage();
        // });

        // $this->app->alias(MyPackage::class, 'my-package');
    }
}
