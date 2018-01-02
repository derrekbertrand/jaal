<?php

namespace CollabCorp\ProjectJSON\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Filesystem\Filesystem;
use CollabCorp\ProjectJSON\Commands\Generators\Generator;

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

        \CollabCorp\ProjectJSON\Commands\Generators\ClassGenerator::class,
        \CollabCorp\ProjectJSON\Commands\Generators\RouteGenerator::class

    ];
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:api {prefix}';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new JSON api class and register it\'s routes';



    /**
     * Data to keep info on api
     *
     * @var Illuminate\Support\Collection $data
     */
    protected $data;

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

        //what were working with
        $args = $this->arguments();

        $generators = static::$generators;

        $options =$this->options();

        //check if the args are available
        if (array_key_exists('prefix', $args)){

            foreach ($generators as $class) {

                $generator = new $class($this->files, $this);

                if ($generator instanceof \CollabCorp\ProjectJSON\Commands\Generators\Generator) {
                    $generator->generate($args['prefix']);
                } else {
                    $this->error("Config Error:{$class} is not a valid Generator object. {$class} not processed!");
                }
            }
        }
    }
}
