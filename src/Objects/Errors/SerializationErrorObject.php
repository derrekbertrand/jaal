<?php

namespace DialInno\Jaal\Objects\Errors;

use Illuminate\Support\Collection;
use DialInno\Jaal\Objects\Errors\ErrorObject;
use DialInno\Jaal\Objects\GenericObject;

/**
 * Responsible for serializing a error object.
 */
class SerializationErrorObject extends ErrorObject {
    public function __construct(GenericObject $parent, $data)
    {
        parent::__construct($parent, $data);

        $this->data = $this->data->merge([
            'title' => 'Serialization Error',
            'status' => '500'
        ]);
    }
}
