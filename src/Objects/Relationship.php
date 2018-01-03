<?php

namespace DialInno\Jaal\Objects;

use Illuminate\Support\Collection;

class Relationship extends BaseObject
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
        $data = $this->payload->get('data');

        if (is_array($data)) {
            $this->unpackObjectArray('data', ResourceIdentifier::class, $path);
        } else if (is_object($data)) {
            $this->unpackObject('data', ResourceIdentifier::class, $path);
        }
        
        $this->unpackObject('meta', Meta::class, $path);
        $this->unpackObject('links', Link::class, $path);

        return $this;
    }

    /**
     * Return a collection of keys; they object must contain at least one.
     *
     * @return Collection
     */
    protected function payloadMustContainOne()
    {
        return Collection::make(['links', 'data', 'meta']);
    }

    /**
     * Return a collection of keys; this is an extensive list of key names.
     *
     * @return Collection
     */
    protected function payloadMayContain()
    {
        return Collection::make(['links', 'data', 'meta']);
    }

    /**
     * Return a collection containing key value pairs of keys and the types that we expect as values.
     *
     * @return Collection
     */
    protected function payloadDatatypes()
    {
        return Collection::make([
            'links' => 'object',
            'data' => 'NULL|array|object',
            'meta' => 'object',
        ]);
    }
}
