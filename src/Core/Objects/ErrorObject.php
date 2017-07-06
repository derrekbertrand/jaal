<?php

namespace DialInno\Jaal\Core\Objects;

use DialInno\Jaal\JsonApi;
use Illuminate\Support\Collection;


/**
 * Responsible for serializing a error object.
 */
class ErrorObject extends MetaObject {

    public function __construct(MetaObject $parent, $data)
    {
        parent::__construct($parent, $data);

        $this->data = (new Collection([
            'title' => 'Error',
            'detail' => 'An error occurred.',
            'status' => '400',
        ]))->merge($this->data);
    }

    public function getStatus()
    {
        return strval($this->data->get('status', '400'));
    }

    public function jsonSerialize()
    {
        return $this->data->only(['id', 'links', 'status', 'code', 'title', 'detail', 'source', 'meta']);
    }
}
