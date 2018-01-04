<?php

namespace DialInno\Jaal\Objects;

use Illuminate\Support\Collection;

class Relationship extends BaseObject
{
    /**
     * Return a array of keys; they object must contain at least one.
     *
     * @return array
     */
    protected function payloadMustContainOne(): array
    {
        return ['links', 'data', 'meta'];
    }

    /**
     * Return a array of keys; this is an extensive list of key names.
     *
     * @return array
     */
    protected function payloadMayContain(): array
    {
        return ['links', 'data', 'meta'];
    }

    /**
     * Return a array containing key value pairs of keys and the types that we expect as values.
     *
     * @return array
     */
    protected function payloadDatatypes(): array
    {
        return [
            'links' => 'object',
            'data' => 'NULL|array|object',
            'meta' => 'object',
        ];
    }

    /**
     * Return a map of keys to object type.
     *
     * @return array
     */
    protected function payloadObjectMap(): array
    {
        return [
            'links' => Link::class,
            'data' => ResourceIdentifier::class,
            'meta' => Meta::class,
        ];
    }
}
