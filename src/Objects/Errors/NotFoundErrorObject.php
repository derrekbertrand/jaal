<?php

namespace DialInno\Jaal\Objects\Errors;

use DialInno\Jaal\JsonApi;
use Illuminate\Support\Collection;
use DialInno\Jaal\Objects\ErrorObject;
use DialInno\Jaal\Objects\MetaObject;

/**
 * Responsible for serializing a error object.
 */
class NotFoundErrorObject extends ErrorObject {
    public function __construct(MetaObject $parent)
    {
        $this->parent = $parent;

        $this->data = new Collection([
            'title' => 'Resource Not Found',
            'detail' => 'The resource could not be found.',
            'status' => '404'
        ]);
    }
}
