<?php

namespace DialInno\Jaal\Commands\Generators;

use Illuminate\Support\Collection;
use Illuminate\Filesystem\Filesystem;
use DialInno\Jaal\Commands\Generators\Generator;

class ClassGenerator extends Generator
{
    /**
     * The api class example.
     *
     * @var static $apiClassPath
     */
    protected static $apiClassTemplatePath = __DIR__."/../Publish/ApiV1.php";

    /**
     * Generate the class if it doesnt exist
     * @var string $api_name
     *
     **/
    public function generate(string $api_name)
    {
        $this->createClass($api_name);
    }

    /**
     * Create a class if it doesnt exist
     * @var string $api_name
     **/
    private function createClass(string $api_name)
    {
        //was a class name given?
        $generatedClassName = $api_name == null ? "ApiV1" : $api_name;
        //default to saving to the app/Http/ dir.
        $class_path = app_path("Http/$generatedClassName.php");
        //Next verify class doesnt already exits
        if (!$this->files->exists($class_path)) {
            
            $this->files->put($class_path, $this->getNewClassContents($generatedClassName,$api_name));

            $this->command->info("Succesfully created app/Http/{$generatedClassName}.php!");

        } else {
            $this->command->error("The class app/Http/{$generatedClassName}.php already exists!");
        }
    }

    /**
     * Returns the new class file contents to 
     * overwrite the template content.
     * @var string $generatedClassName
     * @var array $api_name
     **/
    private function getNewClassContents(string $generatedClassName,string $api_name){
        //get the template
        $newClass = $this->files->get(static::$apiClassTemplatePath);

        $formattedApiName = kebab_case($api_name);
        //Replace the default ApiV1 if needed--Todo cleanup
        $newClass = str_replace('ApiV1', $generatedClassName, $newClass);
        $newClass = str_replace("public static \$version = 'v1';","public static \$version = '{$formattedApiName}';",$newClass);
        $newClass = str_replace("namespace DialInno\Jaal\Commands\Publish;","namespace App\Http;",$newClass);

        return $newClass;
    }

}
