<?php

namespace DialInno\Jaal\Objects;

use Illuminate\Support\Collection;

class Resource extends BaseObject
{
    /**
     * Return a array of keys; the object must contain them all.
     *
     * @return array
     */
    protected function payloadMustContain(): array
    {
        return ['type'];
    }

    /**
     * Return a array of keys; this is an extensive list of key names.
     *
     * @return array
     */
    protected function payloadMayContain(): array
    {
        return ['id', 'type', 'attributes', 'relationships', 'links', 'meta'];
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
            'attributes' => 'object',
            'relationships' => 'object',
            'links' => 'object',
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
            'attributes' => Attributes::class,
            'relationships' => Relationships::class,
            'links' => Link::class,
            'meta' => Meta::class,
        ];
    }

    /**
     * Objects that are safe to cull.
     *
     * @return array
     */
    protected function cullableObjects(): array
    {
        return [
            'attributes',
            'links',
            'meta',
            'relationships',
        ];
    }
}
