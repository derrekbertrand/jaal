<?php

namespace DialInno\Jaal\Core\Errors;

use DialInno\Jaal\Core\JsonApi;
use Illuminate\Support\Collection;
use DialInno\Jaal\Core\Errors\ErrorObject;
use DialInno\Jaal\Core\Objects\GenericObject;

/**
 * Responsible for serializing a error object.
 */
class NotFoundErrorObject extends ErrorObject 
{
    public function __construct(GenericObject $parent)
    {
        $this->parent = $parent;

        $this->data = new Collection([
            'title' => 'Resource Not Found',
            'detail' => 'The resource could not be found.',
            'status' => '404'
        ]);

        //we do not validate, because it is hard coded
    }
}
