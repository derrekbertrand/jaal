<?php

namespace DialInno\Jaal\Tests;


use DialInno\Jaal\Tests\Api\JsonApiV1;
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

        //set up the routes
        //in a real setup there might be some changes to this
        \Route::group([
            'prefix' => 'api/v1',
            'as' => 'api.v1.',
            ], function () {
                JsonApiV1::routes();
        });

        //pull in our factories for testing
        $this->withFactories(__DIR__.'/../database/factories');
    }
    /**
    * Resolve application HTTP exception handler.
    *
    * @param  \Illuminate\Foundation\Application  $app
    * @return void
    */
    protected function resolveApplicationExceptionHandler($app)
    {
        $app->singleton('Illuminate\Contracts\Debug\ExceptionHandler', 'Orchestra\Testbench\Exceptions\Handler');
    }

    protected function disableExceptionHandling()
    {
        $this->app->instance(LaravelHandler::class, new class extends OrchestraHandler {
            public function __construct() {}
            public function report(\Exception $e) {}
            public function render($request, \Exception $e) {
                throw $e;
            }
        });
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
        $app['config']->set('database.default', 'testing');
        //pull in our testing config
        $api = new JsonApiV1;
        $app['config']->set('jaal', $api);

        //make sure we use FK when running code
        \Schema::enableForeignKeyConstraints();
    }

    protected function contentAsObject(TestResponse $r)
    {
        return json_decode($r->getContent());
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
            //'YourProject\YourPackage\YourPackageServiceProvider',
        ];
    }
}
