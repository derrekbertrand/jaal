<?php

namespace DialInno\Jaal\Objects;

use Illuminate\Support\Collection;

class Error extends BaseObject
{
    /**
     * Return a array of keys; this is an extensive list of key names.
     *
     * @return array
     */
    protected function payloadMayContain(): array
    {
        return ['id', 'links', 'status', 'code', 'title', 'detail', 'source', 'meta'];
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
            'links' => 'object',
            'status' => 'string',
            'code' => 'string',
            'title' => 'string',
            'detail' => 'string',
            'source' => 'object',
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
            'source' => ErrorSource::class,
            'meta' => Meta::class,
        ];
    }
}
