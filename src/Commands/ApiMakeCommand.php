<?php

namespace DialInno\Jaal\Commands;

use Illuminate\Console\Command;
use DialInno\Jaal\Core\Api\Version;
use Illuminate\Filesystem\Filesystem;
use DialInno\Jaal\Commands\Generators\Generator;

class ApiMakeCommand extends Command
{


    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;
    /**
     * The command instance.
     *
     * @var \Illuminate\Constole\Command
     */
    protected $command;

    /**
     * The generators that should run to get a new api ready to go.
     *
     * @var \Illuminate\Constole\Command
     */

    protected static $generators =[

        \DialInno\Jaal\Commands\Generators\ClassGenerator::class,
        \DialInno\Jaal\Commands\Generators\RouteGenerator::class

    ];
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jaal:make {api_name} {--api_version=1}';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new Api Class / Routes';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    /**
     * Run each generator to scaffold a new api.
     *
     * @return mixed
     */
    public function handle()
    {


        $args = $this->arguments();

        $generators = static::$generators;

        $options =$this->options();

        if (array_key_exists('api_name', $args) && array_key_exists('api_version', $options)){

            $api_version = new Version($options['api_version']);

            //SomeNameV* : . are replaced with _
            $classNameWanted = $args['api_name']."V{$api_version->getFormattedForClassNameVersion()}";

            foreach ($generators as $class) {
               
                $generator = new $class($this->files, $this, $api_version);

                if ($generator instanceof \DialInno\Jaal\Commands\Generators\Generator) {

                    $generator->generate($classNameWanted);
                } else {
                    $this->error("Config Error:{$class} is not a valid Generator object. {$class} not processed!");
                }
            }
        }
        else{

            $this->error("Invalid Options Error: The jaal:make command requires a name and --api_version option. e.g php artisan jaal:make myApiName --api_version=1.0");
        }
    }

    
}
