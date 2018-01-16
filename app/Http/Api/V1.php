<?php

namespace App\Http\Api;

use DialInno\Jaal\Jaal;

class V1 extends Jaal
{
    /**
     * This is the array that should be passed to Route::group().
     *
     * @var array
     **/
    protected static $route_group_settings = ['prefix' => 'v1', 'as' => 'v1.'];

    protected static $route_controllers = [
        'account' => \App\Http\Controllers\AccountController::class,
        'agent' => \App\Http\Controllers\AgentController::class,
        'contact' => \App\Http\Controllers\ContactController::class,
        'tag' => \App\Http\Controllers\TagController::class,
    ];

    protected static $route_to_one_relations = [
        'account' => ['agent'],
    ];

    protected static $route_to_many_relations = [
        'account' => ['contacts', 'tags'],
        'agent' => ['accounts', 'tags'],
        'contact' => ['accounts', 'tags'],
        'tag' => ['accounts', 'agents', 'contacts'],
    ];
}
