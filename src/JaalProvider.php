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
    protected $commands =[

        Commands\ApiMakeCommand::class

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
        $this->commands($this->commands);
    }
}
