<?php

namespace App\Http;

/**
 * We're simply pulling Orchestra's test kernel and adding what we need to it.
 */
class NegotiationHttpKernel extends \Orchestra\Testbench\Http\Kernel
{
    /**
     * The application's middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \Orchestra\Testbench\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        \DialInno\Jaal\Middleware\NegotiateHttp::class,
        // \Orchestra\Testbench\Http\Middleware\TrustProxies::class,
    ];
}
