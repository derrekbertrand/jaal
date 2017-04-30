<?php

namespace DialInno\Jaal\Objects\Errors;

use DialInno\Jaal\JsonApi;
use Illuminate\Support\Collection;
use DialInno\Jaal\Objects\ErrorObject;
use DialInno\Jaal\Objects\MetaObject;

/**
 * Responsible for serializing a error object.
 */
class ValidationErrorObject extends ErrorObject {
    public function __construct(MetaObject $parent, $data)
    {
        parent::__construct($parent, $data);

        $this->data = $this->data->merge([
            'title' => 'Validation Error',
            'status' => '400'
        ]);
    }
}
