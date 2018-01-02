<?php

namespace CollabCorp\ProjectJSON\Commands\Generators;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use CollabCorp\ProjectJSON\Core\Api\Version;
use Illuminate\Filesystem\Filesystem;

abstract class Generator
{
    /**
     * The command instance.
     *
     * @var \Illuminate\Console\Command
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
     * @param string $prefix
     *
     **/
    abstract public function generate(string $prefix);
}
