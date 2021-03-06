<?php

namespace DialInno\Jaal\Tests;

use App\Http\Api\V1;
use DialInno\Jaal\JaalRouter;
use Illuminate\Foundation\Testing\TestResponse;
use Orchestra\Testbench\Exceptions\Handler as OrchestraHandler;
use Illuminate\Contracts\Debug\ExceptionHandler as LaravelHandler;

class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        parent::setUp();
        // uncomment to enable route filters if your package defines routes with filters
        // $this->app['router']->enableFilters();

        // call migrations specific to our tests
        $this->loadMigrationsFrom(realpath(__DIR__.'/../database/migrations'));
        //pull in our factories for testing
        $this->withFactories(realpath(__DIR__.'/../database/factories'));
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // $app['config']->set('project_json.api_groups', ['v1' => JsonApiV1::class]);

        //make sure we use FK when running code
        \Schema::enableForeignKeyConstraints();
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            \Orchestra\Database\ConsoleServiceProvider::class,
            //'Cartalyst\Sentry\SentryServiceProvider',
            \App\Providers\ServiceProvider::class,
        ];
    }

    protected function callHttp(string $method, string $uri, string $payload)
    {
        return $this->call($method, $uri, [], [], [],[], $payload);
    }
}
