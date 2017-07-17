<?php

namespace DialInno\Jaal\Core\Errors;

use Illuminate\Support\Collection;
use DialInno\Jaal\Core\Objects\ErrorObject;
use DialInno\Jaal\Core\Objects\GenericObject;

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
