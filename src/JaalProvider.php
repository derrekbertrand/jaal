<?php

namespace DialInno\Jaal;

use DialInno\Jaal\Contracts\Response as ResponseContract;
use DialInno\Jaal\Objects\Document;
use DialInno\Jaal\Response;
use DialInno\Jaal\Jaal;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;

class JaalProvider extends ServiceProvider
{
    protected $api_map = [];
    protected $base_path = null;

    /**
     * Get the application path with any segments glued on.
     *
     * @param string|array $segments
     * @return string
     */
    protected function glueBasePath($segments)
    {
        // cache the app path, so we aren't making constant function calls
        // plus, it allows for better test coverage when I don't fudge
        $this->base_path = $this->base_path ?? app_path();

        if (!is_array($segments)) {
            $segments = [$segments];
        }

        array_unshift($segments, $this->base_path);

        return implode(DIRECTORY_SEPARATOR, $segments);
    }

    protected function getLoaders()
    {
        return [
            $this->glueBasePath(['Http', 'Api']) => [
                'prefix' => 'api',
                'as' => 'api.',
                'middleware' => 'api',
            ]
        ];
    }

    /**
     * Find all API classes in the path and create a route group for each.
     **/
    protected function mapApi(string $path, ?array $group_data = null)
    {
        foreach ((new Finder)->in($path)->files() as $class) {
            $class = app()->getNamespace().str_replace(
                ['/', '.php'],
                ['\\', ''],
                Str::after($class->getPathname(), $this->glueBasePath(''))
            );

            $this->api_map[$class] = $group_data;
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // if you are using the RouteServiceProvider, like good little boys and
        // girls, this is all that should be needed to load routes
        if (!$this->app->routesAreCached()) {
            foreach ($this->api_map as $api_class => $group_data) {
                if (!is_array($group_data)) {
                    $api_class::defineRoutes();
                } else {
                    Route::group($group_data, function () use ($api_class) {
                        $api_class::defineRoutes();
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
        // get loader directories and map all the classes in them
        foreach ($this->getLoaders() as $loader_path => $group_data) {
            $this->mapApi($loader_path, $group_data);
        }

        $this->app->singleton(ResponseContract::class, function ($app) {
            return new Response($app->make(Document::class));
        });

        // When a controller typehints Jaal's contract, pass an appropriate class in
        foreach (array_keys($this->api_map) as $api_class) {
            foreach ($api_class::getRouteControllers() as $api_controller) {
                $this->app->addContextualBinding(
                    $api_controller,
                    \DialInno\Jaal\Contracts\Jaal::class,
                    $api_class);
            }
        }

    }
}
