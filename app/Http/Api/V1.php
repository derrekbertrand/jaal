<?php

namespace App\Http\Api;

use DialInno\Jaal\Jaal;

class V1 extends Jaal
{
    /**
     * This array is serailized into every JSON response sent back to the
     * client. Use it to add copyright and disclaimer data to your APIs
     *
     * @var array
     **/
    public static $meta = [
        'copyright' => "Copyright Your Company"
    ];

    /**
     * This is the prefix that should be placed in the route names and URI
     * paths.
     *
     * You can use this to name or version your APIs, so that one server can
     * respond to several sets of endpoints. Maybe you have multiple APIs that
     * you wish to keep separate, or you want to add an updated implementation
     * that exposes more features.
     *
     * @var string
     **/
    public static $api_prefix = 'v1';

    /**
     * This is a list of API resource names and their related configuration.
     *
     * @var array
     **/
    public static $resources = [
        'account'  => [
            'resource' => \DialInno\Jaal\Tests\Resources\AccountResource::class,
            'controller' => \DialInno\Jaal\Tests\Controllers\AccountController::class,
            'to_one' => ['agent'],
            'to_many' => ['contacts', 'tags'],
        ],
        'agent'  => [
            'resource' => \DialInno\Jaal\Tests\Resources\AgentResource::class,
            'controller' => \DialInno\Jaal\Tests\Controllers\AgentController::class,
            'to_many' => ['accounts', 'tags'],
        ],
        'contact'  => [
            'resource' => \DialInno\Jaal\Tests\Resources\ContactResource::class,
            'controller' => \DialInno\Jaal\Tests\Controllers\ContactController::class,
            'to_many' => ['accounts', 'tags'],
        ],
        'tag'  => [
            'resource' => \DialInno\Jaal\Tests\Resources\TagResource::class,
            'controller' => \DialInno\Jaal\Tests\Controllers\TagController::class,
            'to_many' => ['accounts', 'agents', 'contacts'],
        ],
    ];
}
