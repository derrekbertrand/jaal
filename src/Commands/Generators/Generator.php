<?php

namespace DialInno\Jaal\Commands\Generators;

use Illuminate\Console\Command;
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
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * The version of the api/resource
     *
     * @var DialInno\Jaal\Core\Api\Version;
     */
    protected $version;

    public function __construct(Filesystem $files, Command $command)
    {
        $this->files = $files;
        $this->command= $command;

    }

    /**
     * get the version of the api/resource
     *
     * @return DialInno\Jaal\Core\Api\Version;
     */
    public function getVersion()
    {
        return $this->version;
    }
     

    /**
     * Generate the api class resource
     * @var $api_info
     *
     **/
    abstract public function generate(array $api_info);
}
