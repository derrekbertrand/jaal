<?php

namespace DialInno\Jaal\Core\Objects;

use Illuminate\Support\Collection;
use DialInno\Jaal\Core\Api\JsonApi;
use Illuminate\Contracts\Support\Jsonable;
use DialInno\Jaal\Core\Objects\ErrorObject;

/**
 * Responsible for serializing the object.
 */
abstract class GenericObject implements Jsonable, \JsonSerializable {



    protected $data;
    protected $parent;

    public function __construct(GenericObject $parent, $data)
    {
        $this->parent = $parent;
        $this->meta = new Collection;

        if($data instanceof Collection)
            $this->data = $data;
        else if($data instanceof \JsonSerializable)
            $this->data = new Collection($data->jsonSerialize());
        else if(is_array($data))
            $this->data = new Collection($data);
        //not sure what to do with anything else
        else
            throw new \Exception('Jaal objects must be created from an array, collection, or JsonSerializable object.');

        //validate after adding
        $this->validateMembers();
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
     * Friendly wrapper to add meta object.
     *
     * @param  array|Collection|JsonSerializable|ErrorObject $meta_data
     */
    public function addMeta($meta_data)
    {
        if($meta_data instanceof \JsonSerializable)
            $meta_data = new Collection($meta_data->jsonSerialize());
        else if(is_array($meta_data))
            $meta_data = new Collection($meta_data);
        //not sure what to do with anything else
        else if(!($meta_data instanceof Collection))
            throw new \Exception('Jaal meta-objects must be created from an array, collection, or JsonSerializable object.');

        $this->meta = $this->meta->merge($meta_data);

        return $this;
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
                throw new \Exception($key.' is not a valid strict member name.');
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
        return $this->data->all();
    }
}
