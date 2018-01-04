<?php

namespace DialInno\Jaal\Objects;

use Illuminate\Support\Collection;

class ErrorSource extends BaseObject
{
    /**
     * Return a array of keys; this is an extensive list of key names.
     *
     * @return array
     */
    protected function payloadMayContain(): array
    {
        return ['pointer', 'parameter'];
    }

    /**
     * Return a array containing key value pairs of keys and the types that we expect as values.
     *
     * @return array
     */
    protected function payloadDatatypes(): array
    {
        return [
            'pointer' => 'string',
            'parameter' => 'string',
        ];
    }
}
