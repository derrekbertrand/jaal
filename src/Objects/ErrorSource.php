<?php

namespace DialInno\Jaal\Objects;

use Illuminate\Support\Collection;

class ErrorSource extends BaseObject
{
    /**
     * Return a collection of keys; this is an extensive list of key names.
     *
     * @return Collection
     */
    protected function payloadMayContain()
    {
        return Collection::make(['pointer', 'parameter']);
    }

    /**
     * Return a collection containing key value pairs of keys and the types that we expect as values.
     *
     * @return Collection
     */
    protected function payloadDatatypes()
    {
        return Collection::make([
            'pointer' => 'string',
            'parameter' => 'string',
        ]);
    }
}
