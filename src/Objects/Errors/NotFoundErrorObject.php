<?php

namespace DialInno\Jaal\Objects\Errors;

use Illuminate\Support\Collection;
use DialInno\Jaal\Objects\Errors\ErrorObject;
use DialInno\Jaal\Objects\GenericObject;

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
