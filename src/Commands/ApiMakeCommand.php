<?php

namespace DialInno\Jaal\Commands;

use Illuminate\Console\Command;
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
        \DialInno\Jaal\Commands\Generators\RouteGenerator::class,

    ];
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jaal:make {--api=}';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate an ApiV1 Class / Routes';

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
        $options =$this->options();

        if (array_key_exists('api', $options)) {

            //default to ApiV1 if name api value isnt given
            $classNameWanted = $options['api']== null ? 'ApiV1' :$options['api'];

            foreach (static::$generators as $class) {
                $generator = new $class($this->files, $this);

                if ($generator instanceof \DialInno\Jaal\Commands\Generators\Generator) {
                    $generator->generate($classNameWanted);
                } else {
                    $this->error("Config Error:{$class} is not a valid Generator object. {$class} not processed!");
                }
            }
        }
    }
}
