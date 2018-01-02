<?php

namespace DialInno\Jaal\DocObjects;

use Illuminate\Support\Collection;

class Relationships extends DocObject
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

        // each key needs to be a Relationship

        $this->payload->each(function ($relationship, $keyname) use ($path) {
            $this->unpackObject($keyname, Relationship::class, $path);
        });

        return $this;
    }
}
