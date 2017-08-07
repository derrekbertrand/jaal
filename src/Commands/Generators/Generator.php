<?php

namespace DialInno\Jaal\Commands\Generators;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use DialInno\Jaal\Core\Api\Version;
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
     * The filesystem instance.
     *
     * @param \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    public function __construct(Filesystem $files, Command $command)
    {
        $this->files = $files;
        $this->command= $command;
    }

    /**
     * Generate the api class resource
     * @param string $api_name
     *
     **/
    abstract public function generate(string $api_name);
}
