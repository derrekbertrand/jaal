<?php

namespace DialInno\Jaal;

use Validator;
use Exception;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Collection;
use Illuminate\Container\Container;
use DialInno\Jaal\Objects\Attributes;
use DialInno\Jaal\Objects\Document;
use DialInno\Jaal\Objects\Relationships;
use DialInno\Jaal\Objects\Relationship;
use DialInno\Jaal\Objects\Resource;
use DialInno\Jaal\Contracts\Response;
use DialInno\Jaal\Contracts\HydrateResource;

abstract class Schema implements HydrateResource
{
    protected $exception;
    protected $document;
    protected $resource;
    protected $path = [];
    protected $method = '';
    public static $resource_type;

    public function __construct(Response $exception)
    {
        $this->document = new Document;
        $this->exception = $exception;

        if (!is_string(static::$resource_type)) {
            throw new Exception(get_class($this).' must define "public static $resource_type;" as string.');
        }
    }

    public function withPath(array $path)
    {
        $this->path = $path;

        return $this;
    }

    public function withMethod(string $method)
    {
        $this->method = $method;

        return $this;
    }

    /** 
     * Take a Resource its payload and hydrate it.
     */
    public function hydrate(Resource $resource)
    {
        $this->resource = $resource;

        $this->assertResourceAttributesAreValid();
        $this->assertResourceRelationsAreValid();

        $this->exception->throwResponseIfErrors();

        $resource = $this->createHydrated();

        return $resource;
    }

    abstract protected function createHydrated();

    protected function assertResourceAttributesAreValid()
    {
        $scalar_rules_method = 'attributes'.studly_case($this->method).'Rules';

        // if we have rules for this, validate the attributes
        if (method_exists($this, $scalar_rules_method)) {
            $rules = $this->$scalar_rules_method();
            $allowed_keys = array_keys($rules);

            $attr = $this->resource->get('attributes', collect());
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
    }

    protected function assertResourceRelationsAreValid()
    {
        $to_many_map_method = 'toMany'.studly_case($this->method).'Map';
        $to_one_map_method = 'toOne'.studly_case($this->method).'Map';
        $rules = [];
        $allowed_relations = [];
        $relations = $this->resource->get('relationships', collect());

        // contruct a ruleset and whitelist for toMany
        if (method_exists($this, $to_many_map_method)) {
            $map = $this->$to_many_map_method();
            $allowed_relations = array_keys($map);

            // validate the relationships
            foreach($map as $rel_name => $rel_allowed) {
                $rel_allowed = explode('|', $rel_allowed);

                $rules[$rel_name.'.data.*.type'] = 'required_with:'.$rel_name.'.data.*|in:'.implode(',', $rel_allowed);
                $rules[$rel_name.'.data.*.id'] = 'required_with:'.$rel_name.'.data.*';
            }
        }

        // contruct a ruleset and whitelist for toOne
        if (method_exists($this, $to_one_map_method)) {
            $map = $this->$to_one_map_method();
            $allowed_relations = array_merge($allowed_relations, array_keys($map));

            // validate the relationships
            foreach($map as $rel_name => $rel_allowed) {
                $rel_allowed = explode('|', $rel_allowed);

                $rules[$rel_name.'.data.type'] = 'required_with:'.$rel_name.'.data|in:'.implode(',', $rel_allowed);
                $rules[$rel_name.'.data.id'] = 'required_with:'.$rel_name.'.data';
            }
        }

        // ensure that only the whitelisted relationships are present
        // an empty set will not add errors
        $this->exception->disallowedKey($relations->keys()->diff($allowed_relations));

        if (count($rules)) {
            // validate the attribute contents
            $validator = Validator::make(
                $relations->toArray(),
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
    }

    /**
     * Dehydrate a Resource object; pack it into the Document appropriately.
     **/
    public function dehydrate($data, bool $is_primary = true)
    {
        $resource = new Resource(['type' => static::$resource_type]);
        $data_id = $this->resourceId($data);
        $data_attr = new Attributes($this->dehydrateAttributes($data));
        $related = $this->dehydrateRelationships($data);

        // we may not have an ID, it's odd but possible
        if (strlen($data_id)) {
            $resource->put('id', $data_id);
        }

        // only put attributes if we have any
        if (count($data_attr)) {
            $resource->put('attributes', $data_attr);
        }

        // handle relations if they exist
        if (count($related)) {
            //
        }

        return $resource;
    }

    protected function dehydrateAttributes($data): array
    {
        return $data->attributesToArray();
    }

    protected function relationshipSchemas(): array
    {
        return [];
    }

    protected function dehydrateRelationships($data)
    {
        $schemas = $this->relationshipSchemas();
        $related = array_intersect_key($data->getRelations(), $schemas);
        $result = [];

        foreach ($related as $rel_name => $rel_data) {
            if ($rel_data instanceof Collection) {
                $result[$rel_name] = $rel_data->map(function ($rel_data_element) use ($schemas, $rel_name) {
                    return (new $schemas[$rel_name]($this->exception))->dehydrate($rel_data_element, false);
                })->all();
            } else {
                $result[$rel_name] = (new $schemas[$rel_name]($this->exception))->dehydrate($rel_data, false);
            }
        }

        return $result;
    }

    protected function resourceId($data): string
    {
        return $data->getKey();
    }
}
