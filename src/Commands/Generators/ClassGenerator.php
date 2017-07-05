<?php

namespace DialInno\Jaal\Commands\Generators;

use Illuminate\Filesystem\Filesystem;
use DialInno\Jaal\Commands\Generators\Generator;

class ClassGenerator extends Generator
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

    /**
     * Generate the class if it doesnt exist
     *
     * @var string $classNameWanted
     * @var  Illuminate\Console\Command $command
     *
     **/
    public function generate(string $classNameWanted)
    {
        $this->createClass($classNameWanted);
    }

    /**
     * Create a class if it doesnt exist
     *
     *
     * @var string $className
     * @var Illuminate\Console\Command $command
     **/
    private function createClass(string $classNameWanted)
    {
        //was a class name given?
        $classNameWanted = $classNameWanted == null ? "ApiV1" :$classNameWanted;
        //default to saving to the app/Http/ dir.
        $class_path =app_path("Http/{$classNameWanted}.php");
        //Next verify class doesnt already exits
        if (!$this->files->exists($class_path)) {
            //get the template
            $newClass = $this->files->get(static::$apiClassTemplatePath);
            //Replace the default ApiV1 if needed--Todo cleanup
            $newClass = str_replace('ApiV1', $classNameWanted, $newClass);
            //replace the contents of the actual file
            $this->files->put($class_path, $newClass);

            $this->command->info("Succesfully created app/Http/{$classNameWanted}.php!");
        } else {
            $this->command->error("The class app/Http/{$classNameWanted}.php already exists!");
        }
    }
}
