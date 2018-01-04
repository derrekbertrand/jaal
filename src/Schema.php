<?php

namespace DialInno\Jaal;

use Validator;
use DialInno\Jaal\Objects\Resource;
use DialInno\Jaal\Exceptions\ValidationException;

abstract class Schema
{
    protected $exception = null;
    protected $jaal_resource = null;
    protected $path = null;
    protected $method = null;

    public function __construct()
    {
        $this->exception = ValidationException::make();
    }

    /** 
     * Take a Resource its payload and hydrate it.
     */
    public function hydrate(Resource $jaal_resource, string $method, array $path)
    {
        $this->jaal_resource = $jaal_resource;
        $this->path = $path;
        $this->method = $method;

        $this->assertResourceIsValid();

        // $resource = $this->createHydrated();

        return null;
    }

    abstract protected function createHydrated();

    protected function assertResourceIsValid()
    {
        $validator = Validator::make(
            $this->jaal_resource->attributes(),
            $this->{'scalar'.studly_case($this->method).'Rules'}()
        );

        if ($validator->fails()) {
            dd($validator->errors());
        }
    }
}
