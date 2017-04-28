<?php

namespace DialInno\Jaal\Objects;

use DialInno\Jaal\JsonApi;
use DialInno\Jaal\Objects\Errors\SerializationErrorObject;
use Illuminate\Support\Collection;

/**
 * Responsible for serializing a resource object.
 */
class ResourceObject extends MetaObject {

    protected static $obj_name = 'resource';

    protected function validateMembers()
    {
        //todo: not required if generated client side...
        if(!$this->data->has('id'))
            $this->addError(new SerializationErrorObject($this->getDoc(), [
                'detail' => 'ID is required for a resource object.'
            ]));

        if(!$this->data->has('type'))
            $this->addError(new SerializationErrorObject($this->getDoc(), [
                'detail' => 'Type is required for a resource object.'
            ]));

        $this->data->each(function ($item, $key) {
            //if it is not in the allowed members list, complain
            if(array_search($key, ['id', 'type', 'attributes', 'relationships', 'links', 'meta']) === false)
                $this->addError(new SerializationErrorObject($this->getDoc(), [
                    'detail' => $key.' is not a valid member of a resource object.'
                ]));
        });
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->data->only(['id', 'type', 'attributes', 'relationships', 'links', 'meta']);
    }
}
