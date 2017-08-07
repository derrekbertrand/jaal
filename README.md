[![Build Status](https://travis-ci.org/dialinno/jaal.svg?branch=dev)][travis-ci-jaal]
[![Coverage Status](https://coveralls.io/repos/github/dialinno/jaal/badge.svg?branch=dev)][coveralls-jaal]

# About JAAL

JAAL is a "JSON:API Abstraction for [Laravel][laravel]". It is **not** framework agnostic, and is **not** an
un-opinionated solution.

# Installation

Currently, there is no packagist package. The library is not stable.

You can, however, [use this method](https://lornajane.net/posts/2014/use-a-github-branch-as-a-composer-dependency) if
you want to give it a go.

# Getting Started

Add `DialInno\Jaal\JaalProvider::class` to your application's providers array.
Run this to publish the example `config/jaal.php` file:

```bash
php artisan vendor:publish --provider=DialInno\\Jaal\\JaalProvider
```

# Config

From there you will want to configure your API. To do this simply create a class for your api and have it extend `DialInno\Jaal\Api\JsonApi`. From there 
we will want to tell the app what routes you provide, what objects you represent, and
what relationships you present to the world. To do so, we will define a few `static` properties on the API class. The `routes` property maps URL slugs to a controller. The `models` property maps
JSON:API resource types to an Eloquent Model. The `relationships` propert maps a JSON:API resource type to its various
named relations. Check the file out for more documentation. The `version` property defines your api version, this can be slug or number. This allows you to have different versions of your API running if
you so choose. The `meta` property defines any meta data that will sent in the JSON:API response. 

An example file (`app/Api/JsonApiV1.php`) is below:

```php

<?php

namespace App\Http;

use \DialInno\Jaal\Api\JsonApi;

class ApiV1 extends JsonApi
{
    /**
     * The version of the api.
     *
     * @var string
     **/
    public static $version = 'v1';

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
```

# Controllers

Each `route` in your API class maps to one controller. Responding to an API request can be as simple as
this:

```php
//in App\Http\Controllers\Api\V1\UserController

public function index(Request $request)
{
    //instantiate a copy of our JSON:API handler or inject it for laravel to new up
    $json_api = new JsonApiV1;

    //have the API instance infer as much data as possible
    $json_api->inferQueryParam($this)
    //we want to index the users
        ->index();

    //prepare and return a response
    return $json_api->getResponse();
}
```

# Models

It is assumed that you already have your models and relationships created for your app. JAAL expects that each Eloquent
Model that the API works with has a compatible `jsonSerialize` method. The quickest way to get started is to use the
trait this library provides. Add the following trait to each model you want to represent:

```php
use DialInno\Jaal\Api\Traits\SerializeModel;

    //in your class
    class Model {

        use SerializeModel;

        //...
    }
```

# Routes

You did not define a bunch of stuff in a API class for no reason. The routes are generated off of your config file and
your API class. You can define all the endpoints like so:

```php
//in routes/api.php

use App\Api\JsonApiV1;

\Route::group([
    'prefix' => 'api/v1',
    'as' => 'api.v1.',
    ], function () {
        JsonApiV1::routes();
});
```
If using our `jaal:make` command, these routes will be registered for you!


# Commands

Currenty JAAL ships out with 1 artisan command at the moment. 

`jaal:make` helps scaffold out a new example api class, register routes automatically and get you going to start a new api.

Simply provide the name of the new Api:

`php artisan jaal:make FooBar`

This will quickly create a new class `FooBar.php` in the `App\Http` namespace. With some example valid values for your api properties for you to override.  This will also register routes for you in your `api.php` routes file:


```php

Route::group(
    [
        'middleware' => \DialInno\Jaal\Middleware\NegotiateJsonApi::class,
        'prefix' => 'foo-bar',
        'as' => 'api.foo-bar.',
        'namespace' => 'FooBar'
    ],
    function () { 
        \App\Http\FooBar::routes();
    }
);


```

# Middleware / Content Negotiation

By default, generated routes with our `jaal:make` command will automatically register JAAL to run its `NegotiatesJsonApi` middleware. The jsonapi spec has certain <a href='http://jsonapi.org/format/#content-negotiation'>Content-Negotaion</a> that must occur between the server and the client. The `NegotiatesJsonApi` will make sure that the request and response are abiding by this negotiation. Here are some quick docs from the jsonapi docs on this:

### Client Responsibilities:

```
Clients MUST send all JSON API data in request documents with the header Content-Type: application/vnd.api+json without any media type parameters.

Clients that include the JSON API media type in their Accept header MUST specify the media type there at least once without any media type parameters.

Clients MUST ignore any parameters for the application/vnd.api+json media type received in the Content-Type header of response documents.

```
### Server Responsibilities:
```
Servers MUST send all JSON API data in response documents with the header Content-Type: application/vnd.api+json without any media type parameters.

Servers MUST respond with a 415 Unsupported Media Type status code if a request specifies the header Content-Type: application/vnd.api+json with any media type parameters.

Servers MUST respond with a 406 Not Acceptable status code if a request’s Accept header contains the JSON API media type and all instances of that media type are modified with media type parameters.
```

# Exclude middlware.

By default all requests are assumed to negotiate this content. You can exlude the middleware by defining a `negotiates_json_api` property on the calling controller of the request. This property can be set to `false` for excluding all routes or be an array of the that explicitly tells which methods that negotiate the api spec. or to run the middleware on i.e. `$negotiates_json_api=["index", "store"];`




[travis-ci-jaal]: https://travis-ci.org/dialinno/jaal
[coveralls-jaal]: https://coveralls.io/github/dialinno/jaal?branch=dev
[laravel]: http://laravel.com/
