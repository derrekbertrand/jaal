<?php

namespace DialInno\Jaal\Objects;

use Illuminate\Support\Collection;

class ResourceIdentifier extends BaseObject
{
    /**
     * Return a collection of keys; the object must contain them all.
     *
     * @return Collection
     */
    protected function payloadMustContain()
    {
        return Collection::make(['id', 'type']);
    }

    /**
     * Return a collection of keys; this is an extensive list of key names.
     *
     * @return Collection
     */
    protected function payloadMayContain()
    {
        return Collection::make(['id', 'type']);
    }

        /**
     * Return a collection containing key value pairs of keys and the types that we expect as values.
     *
     * @return Collection
     */
    protected function payloadDatatypes()
    {
        return Collection::make([
            'id' => 'string',
            'type' => 'string',
        ]);
    }
}
