<?php

namespace DialInno\Jaal\Tests;

use DialInno\Jaal\Tests\Api\JsonApiV1;
use Illuminate\Http\Response;

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
        $jaal = require(__DIR__.'/../config/jaal.php');
        $app['config']->set('jaal', $jaal);

        //make sure we use FK when running code
        \Schema::enableForeignKeyConstraints();
    }

    protected function contentAsObject(Response $r)
    {
        return json_decode($r->content());
    }
}
