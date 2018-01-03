<?php

namespace DialInno\Jaal\Objects;

use Illuminate\Support\Collection;

class Resource extends BaseObject
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

        $this->unpackObject('attributes', Attributes::class, $path);
        $this->unpackObject('relationships', Relationships::class, $path);
        $this->unpackObject('links', Link::class, $path);
        $this->unpackObject('meta', Meta::class, $path);

        return $this;
    }

    /**
     * Return a collection of keys; the object must contain them all.
     *
     * @return Collection
     */
    protected function payloadMustContain()
    {
        return Collection::make(['type']);
    }

    /**
     * Return a collection of keys; this is an extensive list of key names.
     *
     * @return Collection
     */
    protected function payloadMayContain()
    {
        return Collection::make(['id', 'type', 'attributes', 'relationships', 'links', 'meta']);
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
            'attributes' => 'object',
            'relationships' => 'object',
            'links' => 'object',
            'meta' => 'object',
        ]);
    }
}
