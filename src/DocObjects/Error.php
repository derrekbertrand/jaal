<?php

namespace DialInno\Jaal\DocObjects;

use Illuminate\Support\Collection;

class Error extends DocObject
{
    /**
     * Each type of object must unpack its payload from a collection.
     *
     * @param Collection $payload
     * @param array $path
     *
     * @return DocObject
     */
    public function unpackPayload(Collection $payload, array $path = [])
    {
        $this->payload = $payload;

        $this->unpackObject('source', ErrorSource::class, $path);
        $this->unpackObject('links', Link::class, $path);
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
        return Collection::make(['id', 'links', 'status', 'code', 'title', 'detail', 'source', 'meta']);
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
            'links' => 'object',
            'status' => 'string',
            'code' => 'string',
            'title' => 'string',
            'detail' => 'string',
            'source' => 'object',
            'meta' => 'object',
        ]);
    }
}
