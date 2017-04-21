<?php

return [

    /*
    |--------------------------------------------------------------------------
    | VERSION GROUP
    |--------------------------------------------------------------------------
    | When initializing the API objects you will often be asked to supply a
    | group. Group refers to the name of this array. This allows you to
    | Version your API by grouping config data into self-contained blocks.
    */
    'v1' => [

        /*
        |----------------------------------------------------------------------
        | METADATA
        |----------------------------------------------------------------------
        | This array is serailized into every JSON response sent back to the
        | client. Use it to add copyright and disclaimer data to your APIs.
        */
        'meta' => [
            'copyright' => 'Copyright '.date('Y ').env('EJSONIFY_COPYRIGHT', 'Dialing Innovations')
        ],

        /*
        |----------------------------------------------------------------------
        | ROUTE DEFINITIONS
        |----------------------------------------------------------------------
        | This is a name-value association of a JSON:API 'type' and the
        | controller Laravel should use to respond to requests about that
        | Model. This is used to construct routes automatically.
        |
        | For nested resources, use dot delimited syntax.
        */
        'routes' => [
            'user'  => \DialInno\Jaal\Tests\Controllers\UserController::class,
            // 'post'  => 'PostController',
            // 'skill' => 'SkillController',
        ],

        /*
        |----------------------------------------------------------------------
        | MODEL DEFINITIONS
        |----------------------------------------------------------------------
        | Here we define name-value associations of JSON:API 'types' and
        | Laravel Model objects. These are used to do lookups of associations
        | and allow Models to be described by their types.
        */
        'models' => [
            'user'  => \DialInno\Jaal\Tests\Models\User::class,
            'post'  => \DialInno\Jaal\Tests\Models\Post::class,
            'skill'  => \DialInno\Jaal\Tests\Models\Skill::class,
        ],

        /*
        |----------------------------------------------------------------------
        | RELATIONSHIP DEFINITIONS
        |----------------------------------------------------------------------
        | This defines all the associations that the API can handle.
        */
        'relationships' => [
            'post' => [
                'op' => 'to-one',
            ],
            'skill' => [
                'users' => 'many-to-many',
            ],
            'user' => [
                'posts' => 'to-many',
                'skills' => 'many-to-many',
            ],
        ],
    ],
];
