<?php

namespace DialInno\Jaal\Concerns;

use DialInno\Jaal\Objects\Resource;
use DialInno\Jaal\Objects\Document;
use Validator;
use Exception;

trait Hydrates
{
    public static function hydrate(Document $document, string $method, $id = null)
    {
        $data = $document->get('data');
        $model = null;

        if ($data instanceof Resource) {
            $schema = new static;
            $model = $schema->hydrateResource($data, $method, $id, ['data']);
        }

        $schema->exception->throwResponseIfErrors();

        return $model;
    }

    protected function hydrateResource(Resource $data, string $method, $id, ...$path)
    {
        $this->data = $data;
        $this->method = $method;
        $this->path = $path;
        $model = null;

        // validate scalar data
        $this->validateTypeId($id);
        $this->validateAttributes();

        if (!$this->exception->hasErrors()) {
            $model = (new static::$model)->forceFill($this->data->get('attributes', collect())->toArray());

            // hydrate related data
            // $this->hydrateRelationships($model, $method);
        }

        return $model;
    }

    protected function validateTypeId($id)
    {
        $data_type = $this->data->get('type');
        $data_id = $this->data->get('id');

        // we must be unpacking an appropriate type
        if (static::$resource_type !== $data_type) {
            $this->exception->unexpectedValue(static::$resource_type, $data_type, $this->path, 'type');
        }

        if ($id === false && $data_id !== null) {
            // if it is false it cannot be set
            $this->exception->disallowedKey('id', $this->path, 'id');
        } else if (is_string($id) && $data_id !== $id) {
            // otherwise they must match
            $this->exception->unexpectedValue($id, $data_id, $this->path, 'id');
        }
    }

    protected function validateAttributes()
    {
        $scalar_rules_method = 'attribute'.studly_case($this->method).'Rules';

        // if we have rules for this, validate the attributes
        if (method_exists($this, $scalar_rules_method)) {
            $rules = $this->$scalar_rules_method();
            $allowed_keys = array_keys($rules);

            $attr = $this->data->get('attributes', collect());
            $keys = $attr->keys();

            // ensure that only the whitelisted attributes are present
            // an empty set will not add errors
            $this->exception->disallowedKey($keys->diff($allowed_keys), $this->path, 'attributes');

            // validate the attribute contents
            $validator = Validator::make(
                $attr->toArray(),
                $rules
            );

            if ($validator->fails()) {
                foreach($validator->errors()->toArray() as $key => $details) {
                    foreach($details as $detail) {
                        $this->exception->invalidValue($detail, $this->path, 'attributes', $key);
                    }
                }
            }
        }
    }

    protected function validateRelations()
    {
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
}
