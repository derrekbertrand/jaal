<?php

namespace DialInno\Jaal;

use Validator;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Collection;
use Illuminate\Container\Container;
use DialInno\Jaal\Objects\Resource;
use DialInno\Jaal\Contracts\Response;
use DialInno\Jaal\Contracts\HydrateResource;

abstract class Schema implements HydrateResource
{
    protected $exception = null;
    protected $resource = null;
    protected $path = null;
    protected $method = null;

    public function __construct(Response $exception)
    {
        $this->exception = $exception;
    }

    /** 
     * Take a Resource its payload and hydrate it.
     */
    public function hydrate(Resource $resource, string $method, array $path)
    {
        $this->resource = $resource;
        $this->path = $path;
        $this->method = $method;

        $this->assertResourceIsValid();

        $resource = $this->createHydrated();

        return $resource;
    }

    abstract protected function createHydrated();

    protected function assertResourceIsValid()
    {
        $scalar_rules_method = 'scalar'.studly_case($this->method).'Rules';
        $attr = $this->resource->attributes();
        $keys = Collection::make(array_keys($attr));



        // if we have rules for this, validate the attributes
        if (method_exists($this, $scalar_rules_method)) {
            $rules = $this->$scalar_rules_method();
            $allowed_keys = array_keys($rules);

            // an empty set will not add errors
            $this->exception->disallowedKey($keys->diff($allowed_keys));

            $validator = Validator::make(
                $attr,
                $rules
            );

            if ($validator->fails()) {
                foreach($validator->errors()->toArray() as $key => $details) {
                    foreach($details as $detail) {
                        $this->exception->invalidValue($detail, $this->path);
                    }
                }
            }
        }

        $this->exception->throwResponseIfErrors();
    }
}
