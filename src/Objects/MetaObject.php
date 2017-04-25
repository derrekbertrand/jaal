<?php

namespace DialInno\Jaal\Objects;

use DialInno\Jaal\JsonApi;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Support\Jsonable;

/**
 * Responsible for serializing a meta object.
 */
class MetaObject implements Jsonable, \JsonSerializable {

    protected static $obj_name = 'meta';

    protected $data;

    protected $parent;

    public function __construct(MetaObject $parent, $data)
    {
        $this->parent = $parent;

        if($data instanceof Collection)
            $this->data = $data;
        else if($data instanceof \JsonSerializable)
            $this->data = new Collection($data->jsonSerialize());
        else if(is_array($data))
            $this->data = new Collection($data);
        //not sure what to do with anything else
        else
            throw new \Exception('Jaal objects must be created from an array, collection, or JsonSerializable object.');
    }

    public function getHttpStatus()
    {
        return $this->parent->getHttpStatus();
    }

    /**
     * Send an error to the document root, constructing a path along the way.
     *
     * @param  ErrorObject $error
     * @param  string  $path
     * @return ErrorObject
     */
    public function tossError(ErrorObject $error, Collection $path = null)
    {
        if($path === null)
            $path = new Collection();

        return $this->parent->tossError($error, $path->prepend(static::$obj_name));
    }

    /**
     * Friendly wrapper around tossError.
     *
     * @param  array|Collection|JsonSerializable $error_data
     * @param  Collection $path
     * @return ErrorObject
     */
    public function addError($error_data, Collection $path = null)
    {
        return $this->tossError(new ErrorObject($this->getDoc(), $error_data), $path);
    }

    /**
     * Recursively find the document.
     *
     * @return DocObject
     */
    public function getDoc()
    {
        return $this->parent->getDoc();
    }

    protected function validateMembers()
    {
        $this->data->each(function ($item, $key) {
            $member_regex = '/^([a-zA-Z0-9][a-zA-Z0-9_\\-]*)?[a-zA-Z0-9]$/';

            //throw exception if it is bad
            if(preg_match($member_regex, $key) !== 1)
                $this->addError([
                        'title' => 'Invalid Member',
                        'detail' => $key.' is not a valid member name.'
                    ],
                    new Collection([$key])
                );
        });
    }

    /**
     * Convert the model instance to JSON.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $this->validateMembers();

        return $this->data->all();
    }
}
