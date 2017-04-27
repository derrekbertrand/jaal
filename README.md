[![Build Status](https://travis-ci.org/dialinno/jaal.svg?branch=dev)][travis-ci-jaal]
[![Coverage Status](https://coveralls.io/repos/github/dialinno/jaal/badge.svg?branch=dev)][coveralls-jaal]

# About JAAL

JAAL is a "JSON:API Abstraction for [Laravel][laravel]". It is **not** framework agnostic, and is **not** an
un-opinionated solution.

## Installation

Currently, there is no packagist package. The library is not stable.

You can, however, [use this method](https://lornajane.net/posts/2014/use-a-github-branch-as-a-composer-dependency) if
you want to give it a go.

## Getting Started

Add `DialInno\Jaal\JaalProvider::class` to your application's providers array.
Run this to publish the example `config/jaal.php` file:

```bash
php artisan vendor:publish --provider=DialInno\\Jaal\\JaalProvider
```

### Config

Your config file at `config/jaal.php` is what tells the app what routes you provide, what objects you represent, and
what relationships you present to the world. The `routes` key maps URL slugs to a controller. The `models` key maps
JSON:API resource types to an Eloquent Model. The `relationships` key maps a JSON:API resource type to its various
named relations. Check the file out for more documentation.

The JAAL config nests everything into 'versioned' keys, allowing you to have different versions of your API running if
you so choose. In order for JAAL to figure out which configuration to use for any given version, you'll need to extend
`JsonApi` and set a static variable to that key. This ensures JAAL picks the right configuration for a given set of
API endpoints. An example file (`app/Api/JsonApiV1.php`) is below:

```php
<?php

namespace App\Api;

use DialInno\Jaal\JsonApi;

class JsonApiV1 extends JsonApi
{
    public static $api_version = 'v1';
}
```

### Controllers

Each `route` in your `config/jaal.php` file maps to one controller. Responding to an API request can be as simple as
this:

```php
//in App\Http\Controllers\Api\V1\UserController

public function index(Request $request)
{
    //instantiate a copy of our JSON:API handler
    $json_api = new JsonApiV1;

    //have the API instance infer as much data as possible
    $json_api->inferQueryParam($this)
    //we want to index the users
        ->index();

    //prepare and return a response
    return $json_api->getResponse();
}
```

### Models

It is assumed that you already have your models and relationships created for your app. JAAL expects that each Eloquent
Model that the API works with has a compatible `jsonSerialize` method. The quickest way to get started is to use the
trait this library provides. Add the following trait to each model you want to represent:

```php
use DialInno\Jaal\SerializeModel;

    //in your class
    class Model {

        use SerializeModel;

        //...
    }
```

### Routes

You did not define a bunch of stuff in a config file for no reason. The routes are generated off of your config file and
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


[travis-ci-jaal]: https://travis-ci.org/dialinno/jaal
[coveralls-jaal]: https://coveralls.io/github/dialinno/jaal?branch=dev
[laravel]: http://laravel.com/
