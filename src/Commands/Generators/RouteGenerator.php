<?php

namespace DialInno\Jaal\Commands\Generators;

use Illuminate\Support\Collection;
use Illuminate\Filesystem\Filesystem;
use DialInno\Jaal\Commands\Generators\Generator;

class RouteGenerator extends Generator
{


    /**
     * Register the routes if the class doesnt exist
     *
     * @var string $api_info
     *
     **/
    public function generate(string $api_name)
    {
        $this->registerRoutes($api_name);
    }

    /**
     * Register routes for the given api info
     *
     * @var string $api_info
     **/
    private function registerRoutes(string $api_name)
    {
        $apiBasePath = base_path('routes/api.php');

        $routesFileContents = $this->files->get($apiBasePath);

        $routeSignature = "\App\Http\\$api_name::routes();";

        //check if app/Http/${classNameWanted}.php has routes registered...TODO cleanup
        if ($this->files->exists(app_path("Http/".$api_name.".php")) && str_contains($routesFileContents, $routeSignature)) {
            $this->command->error("It seems app/Http/$api_name.php's api routes are already defined in routes/api.php");
        } else {
            
            //create a api kebab case name for as in route definition e.g 'as'=>'api-name-v1'
            $formattedApiName = kebab_case($api_name);

            //Not the cleanest way but keeps text formatted
            $content =<<<PHP
Route::group(
    [
        'middleware' => \DialInno\Jaal\Middleware\NegotiateJsonApi::class,
        'prefix' => '{$formattedApiName}',
        'as' => 'api.{$formattedApiName}.',
        'namespace' => '{$api_name}'
    ],
    function () { 
        \App\Http\\$api_name::routes();
    }
);

PHP;
          
            //tack on at the end of the routes file.
            $routes = $routesFileContents."\n".$content;
            //save the file
            $this->files->put($apiBasePath, $routes);

            $this->command->info("Succesfully registered app/Http/{$api_name}.php's routes!");
        }
    }
}

