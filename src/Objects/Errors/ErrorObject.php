<?php

namespace DialInno\Jaal\Objects\Errors;

use DialInno\Jaal\Api\JsonApi;
use Illuminate\Support\Collection;
use DialInno\Jaal\Objects\GenericObject;

/**
 * Responsible for serializing a error object.
 */
class ErrorObject extends GenericObject
{
    public function __construct(GenericObject $parent, $data)
    {
        parent::__construct($parent, $data);

        $this->parent = $parent;
        
        $this->data = (new Collection([
            'title' => 'Error',
            'detail' => 'An error occurred.',
            'status' => '400',
        ]))->merge($this->data);
    }

    /**
     * Return the statuscode
     * @return string
     **/
    public function getStatus()
    {
        return strval($this->data->get('status', '400'));
    }
    
    /**
     * Return the statuscode
     * @return string
     **/
    public function jsonSerialize()
    {
        return $this->data->only(['id', 'links', 'status', 'code', 'title', 'detail', 'source', 'meta']);
    }
}
