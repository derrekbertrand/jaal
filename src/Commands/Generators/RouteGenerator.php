<?php

namespace DialInno\Jaal\Commands\Generators;

use Illuminate\Filesystem\Filesystem;
use DialInno\Jaal\Commands\Generators\Generator;

class RouteGenerator extends Generator
{


    /**
     * Register the routes if the class doesnt exist
     *
     * @var array $api_info
     * 
     **/
    public function generate(array $api_info)
    {
        $this->registerRoutes($api_info);
    }

    /**
     * Register routes for a given api class
     *
     * @var array $className
     **/
    private function registerRoutes(array $api_info)
    {
        $apiBasePath = base_path('routes/api.php');

        $routesFileContents = $this->files->get($apiBasePath);

        $full_name = $api_info['full_name'];
        //remove brackets that show up during classNameWanted output...TODO //cleanup, shouldnt have to do this??
        $routeSignature = str_replace("{","","\App\Http\{$full_name}::routes();");

        $routeSignature = str_replace("}","",$routeSignature);

        //check if app/Http/${classNameWanted}.php has routes registered...TODO cleanup
        if ($this->files->exists(app_path("Http\\".$full_name.".php")) && str_contains($routesFileContents, $routeSignature)) {
            $this->command->error("It seems app/Http/{$full_name}.php's api routes are already defined in routes/api.php");
        } else {
        //Not the cleanest way but keeps text formatted
        $formattedApiName = kebab_case($api_info['name']);
        $content =<<<PHP
Route::group(['prefix' => '{$api_info['version']}','as' => '{$formattedApiName}.{$api_info['version']}.','namespace' => 'Api'], function () { 
    \App\Http\\{$full_name}::routes();
});
PHP;
          
            //tack on at the end of the routes file.
            $routes = $routesFileContents."\n".$content;
            //save the file
            $this->files->put($apiBasePath, $routes);

            $this->command->info("Succesfully registered app/Http/{$full_name}.php's api version: '{$api_info['version']}' routes!");
        }
    }
}
