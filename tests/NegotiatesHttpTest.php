<?php

namespace DialInno\Jaal\Tests;

use DialInno\Jaal\Objects\Document;
use DialInno\Jaal\Exceptions\BadDocumentException;
use DialInno\Jaal\Exceptions\KeyException;
use DialInno\Jaal\Exceptions\ValueException;

// THE SYSTEM SHOULD:
// - Reject unknown lowercase query parameters
// - Reject non-recommended query parameters
// - Require a relevant Accept header
// - Reject Content-Type & Accept headers that aren't to spec
class NegotiatesHttpTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        \Orchestra\Testbench\TestCase::setUp();

        \Route::get('test', function () { return 'OK'; });
        \Route::post('test', function () { return 'OK'; });
    }

    /**
     * Resolve application HTTP Kernel implementation.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function resolveApplicationHttpKernel($app)
    {
        $app->singleton(\Illuminate\Contracts\Http\Kernel::class, \DialInno\Jaal\Tests\Kernels\NegotiationHttpKernel::class);
    }

    // -------------------------------------------------------------------------
    // QUERY PARAMETERS
    // -------------------------------------------------------------------------

    public function testReservedParameters()
    {
        // all headers satisfied, you can't use reserved query params

        $response = $this->withHeaders([
            'Content-Type' => 'application/vnd.api+json',
            'Accept' => 'application/vnd.api+json',
        ])->get('/test?foo=bar&baz=bax');

        $response->assertStatus(400)
            ->assertSee('\'foo\' is a reserved query parameter')
            ->assertSee('\'baz\' is a reserved query parameter');
    }

    public function testInvalidParameters()
    {
        // all headers satisfied, you can't use reserved query params

        $response = $this->withHeaders([
            'Content-Type' => 'application/vnd.api+json',
            'Accept' => 'application/vnd.api+json',
        ])->get('/test?_foo=bar&baz-=bax');

        $response->assertStatus(400)
            ->assertSee('\'_foo\' is not a valid query parameter')
            ->assertSee('\'baz-\' is not a valid query parameter');
    }

    public function testAllGood()
    {
        // Laravel mixes all input; I'm specifically testing to see that we
        // check query parameters and not things in the body

        $response = $this->call('POST', '/test', [], [], [],
            ["CONTENT_TYPE" => "application/vnd.api+json","HTTP_ACCEPT" => "application/vnd.api+json"],
            '{"data": {"id": "4", "type": "account"}}');

        $response->assertStatus(200)
            ->assertSee('OK');
    }

    // Laravel seems to be sanitizing these characters out of it's input

    // public function testInvalidParameters()
    // {
    //     $response = $this->withHeaders([
    //         'Content-Type' => 'application/vnd.api+json',
    //         'Accept' => 'application/vnd.api+json',
    //     ])->get('/test?deep.blue=snarf&HAL%209000=snarf');

    //     $response->assertStatus(400)
    //         ->assertSee('deep.blue is not a valid query parameter')
    //         ->assertSee('HAL 9000 is not a valid query parameter');
    // }

    // -------------------------------------------------------------------------
    // HTTP HEADERS
    // -------------------------------------------------------------------------

    public function testRequiresHeaders()
    {
        $response = $this->json('GET', '/test');

        $response->assertStatus(415);
    }

    public function testRequresAcceptHeaders()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'application/vnd.api+json',
        ])->get('/test?'.http_build_query(['foo' => 'bar', 'baz' => 'bax']));

        $response->assertStatus(406);
    }


    public function testProviderProvides()
    {
        // provides is never really used, but we'll stub a test out to check it is there
        // get it out of our low coverage listings

        $provider = new \DialInno\Jaal\JaalProvider($this->app);

        $this->assertGreaterThan(0, count($provider->provides()));
    }
}
