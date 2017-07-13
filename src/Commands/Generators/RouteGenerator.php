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
     * Register routes for the given api info
     *
     * @var array $api_info
     **/
    private function registerRoutes(array $api_info)
    {
        $apiBasePath = base_path('routes/api.php');

        $routesFileContents = $this->files->get($apiBasePath);

        $full_name = $api_info['full_name'];

        $routeSignature = "\App\Http\\$full_name::routes();";


        //check if app/Http/${classNameWanted}.php has routes registered...TODO cleanup
        if ($this->files->exists(app_path("Http\\".$full_name.".php")) && str_contains($routesFileContents, $routeSignature)) {
            $this->command->error("It seems app/Http/$full_name.php's api routes are already defined in routes/api.php");
        } else {
            
            //create a api kebab case name for as in route definition e.g 'as'=>'api-name-v1'
            $formattedApiName = kebab_case($api_info['name']);

            //Not the cleanest way but keeps text formatted
            $content =<<<PHP
Route::group(['prefix' => '{$api_info['version']}','as' => '{$formattedApiName}.{$api_info['version']}.','namespace' => '{$api_info['name']}'], function () { 
    \App\Http\\{$full_name}::routes();
});
PHP;
          
            //tack on at the end of the routes file.
            $routes = $routesFileContents."\n".$content;
            //save the file
            $this->files->put($apiBasePath, $routes);

            $this->command->info("Succesfully registered app/Http/{$full_name}.php's routes! api version: '{$api_info['version']}'");
        }
    }

}
