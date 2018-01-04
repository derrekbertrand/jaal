<?php

namespace DialInno\Jaal\Objects;

use Illuminate\Support\Collection;

class ResourceIdentifier extends BaseObject
{
    /**
     * Return a array of keys; the object must contain them all.
     *
     * @return array
     */
    protected function payloadMustContain(): array
    {
        return ['id', 'type'];
    }

    /**
     * Return a array of keys; this is an extensive list of key names.
     *
     * @return array
     */
    protected function payloadMayContain(): array
    {
        return ['id', 'type'];
    }

    /**
     * Return a array containing key value pairs of keys and the types that we expect as values.
     *
     * @return array
     */
    protected function payloadDatatypes(): array
    {
        return [
            'id' => 'string',
            'type' => 'string',
        ];
    }
}
