<?php

namespace CollabCorp\ProjectJSON\Commands\Generators;

use Illuminate\Support\Collection;
use Illuminate\Filesystem\Filesystem;
use CollabCorp\ProjectJSON\Commands\Generators\Generator;

class ClassGenerator extends Generator
{
    /**
     * The api class example.
     *
     * @var static $apiClassPath
     */
    protected static $apiClassTemplatePath = __DIR__."/../Publish/ApiTemplate.php";

    /**
     * Generate the class if it doesnt exist
     * @param string $prefix
     *
     **/
    public function generate(string $prefix)
    {
        $this->createClass($prefix);
    }

    /**
     * Create a class if it doesnt exist
     * @param string $prefix
     **/
    private function createClass(string $prefix)
    {

        $generatedClassName = $prefix == null ? "V1" : $prefix;

        $class_path = app_path("Http/Api/$generatedClassName.php");

        //create the api dir if its not there.
        if (!$this->files->exists(app_path("Http/Api/"))){

            mkdir(app_path("Http/Api/"));
        }
        //Next verify class doesnt already exits
        if (!$this->files->exists($class_path)) {

            $this->files->put($class_path, $this->getNewClassContents($generatedClassName,$prefix));

            $this->command->info("Succesfully created app/Http/Api/{$generatedClassName}.php!");

        } else {
            $this->command->error("The class app/Http/Api/{$generatedClassName}.php already exists!");
        }
    }

    /**
     * Returns the new class file contents to
     * overwrite the template content.
     * @param string $generatedClassName
     * @param array $prefix
     **/
    private function getNewClassContents(string $generatedClassName,string $prefix){

        $newClass = $this->files->get(static::$apiClassTemplatePath);

        $formattedApiName = kebab_case($prefix);

        $newClass = str_replace('ApiTemplate', $generatedClassName, $newClass);
        $newClass = str_replace("public static \$version = 'v1';","public static \$version = '{$formattedApiName}';",$newClass);
        $newClass = str_replace("namespace CollabCorp\ProjectJSON\Commands\Publish;","namespace App\Http\Api;",$newClass);

        return $newClass;
    }

}
