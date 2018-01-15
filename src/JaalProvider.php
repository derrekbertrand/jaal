<?php

namespace DialInno\Jaal;

use Illuminate\Support\ServiceProvider;
use DialInno\Jaal\Contracts\Response as ResponseContract;
use DialInno\Jaal\JaalRouter;
use Illuminate\Support\Facades\Route;
use DialInno\Jaal\Objects\Document;
use DialInno\Jaal\Response;
use DialInno\Jaal\Jaal;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;

class JaalProvider extends ServiceProvider
{
    protected $api_map = [];

    protected function getLoaders()
    {
        return [
            app_path().DIRECTORY_SEPARATOR.'Http'.DIRECTORY_SEPARATOR.'Api' => 'api'
        ];
    }

    /**
     * Find all API classes in the path and create a route group for each.
     **/
    protected function mapApi(string $path, ?string $group = null)
    {
        foreach ((new Finder)->in($path)->files() as $class) {
            $class = '\\'.app()->getNamespace().str_replace(
                ['/', '.php'],
                ['\\', ''],
                Str::after($class->getPathname(), app_path().DIRECTORY_SEPARATOR)
            );

            $this->api_map[$class] = $group;
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // get loader directories and map all the classes in them
        foreach ($this->getLoaders() as $loader_path => $loader_group) {
            $this->mapApi($loader_path, $loader_group);
        }

        // if you are using the RouteServiceProvider, like good little boys and
        // girls, this is all that should be needed to load routes
        if (!$this->app->routesAreCached()) {
            foreach ($this->api_map as $api_class => $api_group_name) {
                if ($api_group_name === null) {
                    JaalRouter::routes($api_class);
                } else {
                    Route::prefix($api_group_name)
                        ->name($api_group_name)->group(function () use ($api_class) {
                            JaalRouter::routes($api_class);
                        });
                }
            }
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        // devs may typehint the response contract
        $this->app->singleton(ResponseContract::class, function () {
            return new Response($this->app->make(Document::class));
        });
    }
}
