<?php

namespace DialInno\Jaal\Objects\Errors;

use DialInno\Jaal\JsonApi;
use Illuminate\Support\Collection;
use DialInno\Jaal\Objects\Errors\ErrorObject;
use DialInno\Jaal\Objects\GenericObject;

/**
 * Responsible for serializing a error object.
 */
class ValidationErrorObject extends ErrorObject {
    public function __construct(GenericObject $parent, $data)
    {
        parent::__construct($parent, $data);

        $this->data = $this->data->merge([
            'title' => 'Validation Error',
            'status' => '400'
        ]);
    }
}
