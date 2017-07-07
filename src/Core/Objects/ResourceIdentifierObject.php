<?php

namespace DialInno\Jaal\Objects;

use DialInno\Jaal\JsonApi;
use Illuminate\Support\Collection;

/**
 * Responsible for serializing a data object.
 */
class ResourceIdentifierObject extends ResourceObject
{

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->data->only(['id', 'type']);
    }
}
