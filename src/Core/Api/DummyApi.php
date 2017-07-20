<?php

namespace DialInno\Jaal\Core\Api;

use \DialInno\Jaal\Core\Api\JsonApi;

class DummyApi extends JsonApi
{
    /**
     * The version of the api.
     *
     * @var string
     **/
    public static $version = '';

    /**
     * This array is serailized into every JSON response sent back to the
     * client. Use it to add copyright and disclaimer data to your APIs
     *
     * @var array
     **/
    protected static $meta = [
        // 'copyright' => "Copyright Dialing Innovations"
    ];

    /**
     * This is a name-value association of a JSON:API 'type' and the
     * controller Laravel should use to respond to requests a{$className}bout that
     * Model. This is used to construct routes automatically.
     *
     * For nested resources, use dot delimited syntax.
     *
     * @var array
     **/
    protected static $routes = [
        // 'user'  => \App\Http\Controllers\Api\V1\UserController::class,
    ];

    /**
     * Here we define name-value associations of JSON:API 'types' and
     * Laravel Model objects. These are used to do lookups of associations
     * and allow Models to be described by their types.
     *
     * @var array
     **/
    protected static $models = [
        // 'user'  => \App\User::class,
    ];

    /**
     * This defines all the associations that the API can handle.
     *
     * @var array
     **/
    protected static $relationships = [
        // 'user' => [
        //     'posts' => 'to-many',
        //     'skills' => 'many-to-many',
        // ],
        // 'post' => [
        //     'op' => 'to-one',
        // ],
        // 'skill' => [
        //     'users' => 'many-to-many',
        // ]
    ];

    /**
     * List which model types include pagination data. This should not be
     * done on models that have large numbers of records..
     *
     * @var array
     **/
    protected static $pagination_data = [];
}
