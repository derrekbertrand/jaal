<?php

namespace DialInno\Jaal\Commands\Generators;

use Illuminate\Filesystem\Filesystem;
use DialInno\Jaal\Commands\Generators\Generator;

class RouteGenerator extends Generator
{


    /**
     * Register the routes if the class doesnt exist
     *
     * @var string $classNameWanted
     * @var Illuminate\Console\Command $command
     **/
    public function generate(string $classNameWanted)
    {
        $this->registerRoutes($classNameWanted);
    }

    /**
     * Register routes for a given api class
     *
     * @var string $className
     * @var string $routesFile
     **/
    private function registerRoutes($classNameWanted)
    {
        $apiBasePath = base_path('routes/api.php');

        $routesFileContents = $this->files->get($apiBasePath);
        //check if app/Http/${classNameWanted}.php has routes registered...TODO cleanup
        if ($this->files->exists(app_path("Http\\".$classNameWanted.".php")) && str_contains($routesFileContents, "{$classNameWanted}::routes();")) {
            $this->command->error("It seems app/Http/{$classNameWanted}.php's api routes are already defined in routes/api.php");
        } else {
            //Not the cleanest way --Todo: cleanup -left align?
        $content =<<<PHP
Route::group(['prefix' => 'v1','as' => 'api.v1.','namespace' => 'Api'], function () { 
    App\Http\\{$classNameWanted}::routes();
});
PHP;
          
            //tack on at the end of the routes file.
            $routes = $routesFileContents."\n".$content;
            //save the file
            $this->files->put($apiBasePath, $routes);

            $this->command->info("Succesfully registered app/Http/{$classNameWanted}.php's api routes!");
        }
    }
}
