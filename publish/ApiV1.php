<?php

namespace App\Http;

/*This is the class that is used as a template for make command*/
class ApiV1 extends \DialInno\Jaal\Core\Api\JsonApi
{
    public static $version = '1.0';

    /**
     * This array is serailized into every JSON response sent back to the 
     * client. Use it to add copyright and disclaimer data to your APIs
     *
     * @var array
     **/
    protected static $meta = [
        'copyright' => "Copyright Dialing Innovations"
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
        'user'  => \DialInno\Jaal\Tests\Controllers\UserController::class,
        // 'post'  => 'PostController',
        // 'skill' => 'SkillController',
    ];

    /**
     * Here we define name-value associations of JSON:API 'types' and
     * Laravel Model objects. These are used to do lookups of associations
     * and allow Models to be described by their types.
     *
     * @var array
     **/
    //Todo...existing model property..named api_models for now.
    protected static $models = [
        'user'  => \DialInno\Jaal\Tests\Models\User::class,
        // 'post'  => \DialInno\Jaal\Tests\Models\Post::class,
        // 'skill'  => \DialInno\Jaal\Tests\Models\Skill::class,
    ];

    /**
     * This defines all the associations that the API can handle.
     *
     * @var array
     **/
    protected static $relationships = [
        'user' => [
            'posts' => 'to-many',
            'skills' => 'many-to-many',
        ],
        // 'post' => [
        //     'op' => 'to-one',
        // ],
        // 'skill' => [
        //     'users' => 'many-to-many',
        // ]
    ];
}
