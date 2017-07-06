<?php

namespace DialInno\Jaal\Commands\Generators;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

abstract class Generator
{

    /**
     * The command instance.
     *
     * @var \Illuminate\Constole\Command
     */
    protected $command;
    /**
     * The api class example.
     *
     * @var static $apiClassTemplatePath
     */
    protected static $apiClassTemplatePath = __DIR__."/../../../publish/ApiV1.php";
    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    public function __construct(Filesystem $files, Command $command)
    {
        $this->files = $files;
        $this->command= $command;
    }

    /**
     * undocumented function
     *
     * @var $options
     * @var  Illuminate\Console\Command $command
     *
     **/
    abstract public function generate(string $options);
}
