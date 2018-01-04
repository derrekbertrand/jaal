<?php

namespace DialInno\Jaal\Objects;

use Illuminate\Support\Collection;

class JsonApi extends BaseObject
{
    /**
     * Return a array of keys; this is an extensive list of key names.
     *
     * @return array
     */
    protected function payloadMayContain(): array
    {
        return ['version', 'meta'];
    }

    /**
     * Return a array containing key value pairs of keys and the types that we expect as values.
     *
     * @return array
     */
    protected function payloadDatatypes(): array
    {
        return [
            'version' => 'string',
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
        return ['meta' => Meta::class];
    }
}
