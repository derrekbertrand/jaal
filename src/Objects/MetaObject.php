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
     * Friendly wrapper to add an error.
     *
     * @param  array|Collection|JsonSerializable|ErrorObject $error_data
     * @return ErrorObject
     */
    public function addError($error_data)
    {
        if($error_data instanceof ErrorObject)
            return $this->parent->addError($error_data);
        else
            return $this->parent->addError(new ErrorObject($this->getDoc(), $error_data));
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
