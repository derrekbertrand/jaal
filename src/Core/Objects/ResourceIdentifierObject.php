<?php

namespace DialInno\Jaal\Core\Objects;

use DialInno\Jaal\JsonApi;
use Illuminate\Support\Collection;
use DialInno\Jaal\Core\Objects\ResourceObject;

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
