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

        $this->assertResourceAttributesAreValid();

        $resource = $this->createHydrated();

        return $resource;
    }

    abstract protected function createHydrated();

    protected function assertResourceAttributesAreValid()
    {
        $scalar_rules_method = 'scalar'.studly_case($this->method).'Rules';

        // if we have rules for this, validate the attributes
        if (method_exists($this, $scalar_rules_method)) {
            $rules = $this->$scalar_rules_method();
            $allowed_keys = array_keys($rules);

            $attr = $this->resource->attributes();
            $keys = $attr->keys();

            // ensure that only the whitelisted attributes are present
            // an empty set will not add errors
            $this->exception->disallowedKey($keys->diff($allowed_keys));

            // validate the attribute contents
            $validator = Validator::make(
                $attr->toArray(),
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

    protected function assertResourceRelationsAreValid()
    {
        $relation_map_method = camel_case($this->method).'RelationMap';

        // if we have a map, validate the relationships
        if (method_exists($this, $relation_map_method)) {
            $map = $this->$relation_map_method();
            $allowed_relations = array_keys($map);

            $relations = $this->resource->relations();
            $rel_keys = Collection::make(array_keys($relations));

            // ensure that only the whitelisted relationships are present
            // an empty set will not add errors
            $this->exception->disallowedKey($rel_keys->diff($allowed_relations));

            // validate the relationship contents
            foreach ($relations as $relation_name => $relationship) {
                // $map[$relation_name];

            }
        }

        $this->exception->throwResponseIfErrors();
    }
}
