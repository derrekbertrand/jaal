<?php

namespace DialInno\Jaal\Commands\Helpers;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class Generator
{
	/**
	 * The api class example.
	 *
	 * @var static $apiClassPath
	 */
	protected static $apiClassPath = __DIR__."/../../../publish/ApiV1.php";
	/**
	 * The filesystem instance.
	 *
	 * @var \Illuminate\Filesystem\Filesystem
	 */
	protected $files;

	public function __construct(Filesystem $files)
	{
		$this->files = $files;
	}

	/**
	 * undocumented function
	 *
	 * @var $options
	 * @var  Illuminate\Console\Command $command
	 * @author 
	 **/
	public function generate($options=[], Command $command)
	{
		//todo: clean this up
		if (array_key_exists('api', $options)) {
			$this->setUpApiFiles($options['api'],$command);
		}
	}

	/**
	 * Create a class if it doesnt exist
	 * and register the routes if needed
	 *
	 * @var string $className
	 * @var Illuminate\Console\Command $command
	 **/
	private function setUpApiFiles($className,$command){

		$className = $className == null ? "ApiV1" :$className;

		$class_path =app_path("Http/{$className}.php");
		
		if(!$this->files->exists($class_path)) {
			//get the template
			$newClass = $this->files->get(static::$apiClassPath);
			//Todo cleanup
			$newClass = str_replace('ApiV1', $className,$newClass);

			$this->files->put($class_path, $newClass);
			$command->info("Successfully created app/Http/{$className}.php!");
			//TODO register routes if not registered yet
			$routesFile = $this->files->get(base_path('routes/api.php'));
			
			$this->registerRoutes($className,base_path('routes/api.php'));
			
			$command->info("Successfully registered app/Http/{$className}.php's Api routes!");

			
		}
		else{

			$command->error("The class 'app/Http/{$className}.php' already exists!");
		}
	}

	/**
	 * Register routes for a given api class
	 *
	 * @var string $className
	 * @var string $routesFile
	 **/
	private function registerRoutes($className,$routesFile){

		// $routesFile = $this->files->get($routesFile);
		// $routes = "
		// 	Route::group([
		// 		'prefix' => 'v1',
		// 		'as' => 'api.v1.',
		// 		'namespace' => 'Api'
		// 		], function () {
		// 			App\Http\{$className}::routes();
		// 	});";

		// $routes = $routesFile."\n".$routes;
		// $this->files->put($routesFile,$routes);
	}

}