<?php

namespace DialInno\Jaal\Objects;

use Illuminate\Support\Collection;

class JsonApi extends BaseObject
{
    /**
     * Each type of object must unpack its payload from a collection.
     *
     * @param Collection $payload
     * @param array $path
     *
     * @return BaseObject
     */
    public function unpackPayload(Collection $payload, array $path = [])
    {
        $this->payload = $payload;

        $this->unpackObject('meta', Meta::class, $path);

        return $this;
    }

    /**
     * Return a collection of keys; this is an extensive list of key names.
     *
     * @return Collection
     */
    protected function payloadMayContain()
    {
        return Collection::make(['version', 'meta']);
    }

    /**
     * Return a collection containing key value pairs of keys and the types that we expect as values.
     *
     * @return Collection
     */
    protected function payloadDatatypes()
    {
        return Collection::make([
            'version' => 'string',
            'meta' => 'object',
        ]);
    }
}
