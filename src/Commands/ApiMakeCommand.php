<?php

namespace DialInno\Jaal\Commands;

use Illuminate\Console\Command;
use DialInno\Jaal\Commands\Helpers\Generator;


class ApiMakeCommand extends Command
{
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
    public function __construct(Generator $generator)
    {
        parent::__construct();
        $this->generator = $generator;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->generator->generate($this->options(), $this);
    }
}
