<?php

namespace CollabCorp\ProjectJSON\Commands\Generators;

use Illuminate\Support\Collection;
use Illuminate\Filesystem\Filesystem;
use CollabCorp\ProjectJSON\Commands\Generators\Generator;

class RouteGenerator extends Generator
{


    /**
     * Register the routes if the class doesnt exist
     *
     * @param string $api_info
     *
     **/
    public function generate(string $prefix)
    {
        $this->registerRoutes($prefix);
    }

    /**
     * Register routes for the given api info
     *
     * @param string $api_info
     **/
    private function registerRoutes(string $prefix)
    {
        $apiBasePath = base_path('routes/api.php');

        $routesFileContents = $this->files->get($apiBasePath);

        $routeSignature = "\App\Http\Api\\$prefix::routes();";

        //check if app/Http/${classNameWanted}.php has routes registered...TODO cleanup
        if ($this->files->exists(app_path("Http/Api/".$prefix.".php")) && str_contains($routesFileContents, $routeSignature)) {
            $this->command->error("It seems app/Http/Api/$prefix.php's api routes are already defined in routes/api.php");
        } else {

            //create a api kebab case name for as in route definition e.g 'as'=>'api-name-v1'
            $formattedApiName = kebab_case($prefix);

            //Not the cleanest way but keeps text formatted
            $content =<<<PHP
Route::group(
    [
        'middleware' => CollabCorp\ProjectJSON\Middleware\NegotiateJsonApi::class,
        'prefix' => \App\Http\Api\\$prefix::\$version,
        'as' => "api.".\App\Http\Api\\$prefix::\$version ."."
    ],
    function () {
        \App\Http\Api\\$prefix::routes();
    }
);

PHP;

            //tack on at the end of the routes file.
            $routes = $routesFileContents."\n".$content;
            //save the file
            $this->files->put($apiBasePath, $routes);

            $this->command->info("Succesfully registered app/Http/Api/{$prefix}.php's routes!");
        }
    }
}

