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
    protected static $apiClassTemplatePath = __DIR__."/../../../publish/ApiV1.php";

    /**
     * Generate the class if it doesnt exist
     * @var array $api_info
     *
     **/
    public function generate(array $api_info)
    {
        $this->createClass($api_info);
    }

    /**
     * Create a class if it doesnt exist
     * @var array $api_info
     **/
    private function createClass(array $api_info)
    {
        //was a class name given?
        $generatedClassName = $api_info['name'] == null ? "ApiV{$api_info['version']}" :$api_info['full_name'];
        //default to saving to the app/Http/ dir.
        $class_path =app_path("Http/{$generatedClassName}.php");
        //Next verify class doesnt already exits
        if (!$this->files->exists($class_path)) {
            
            $this->files->put($class_path, $this->getNewClassContents($generatedClassName,$api_info));

            $this->command->info("Succesfully created app/Http/{$generatedClassName}.php!");
        } else {
            $this->command->error("The class app/Http/{$generatedClassName}.php already exists!");
        }
    }

    /**
     * Returns the new class file contents to 
     * overwrite the template content.
     * @var string $generatedClassName
     * @var array $api_info
     **/
    private function getNewClassContents(string $generatedClassName,array $api_info){
        //get the template
        $newClass = $this->files->get(static::$apiClassTemplatePath);
        //Replace the default ApiV1 if needed--Todo cleanup
        $newClass = str_replace('ApiV1', $generatedClassName, $newClass);
        $newClass = str_replace("public static \$version = 'v1';","public static \$version = '{$api_info['version']}';",$newClass);

        return $newClass;
    }
}
