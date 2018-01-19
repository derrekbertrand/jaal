<?php

namespace DialInno\Jaal\Objects;

use Illuminate\Support\Collection;

class Relationships extends BaseObject
{
    /**
     * Return a map of keys to object type.
     *
     * @return array
     */
    protected function payloadObjectMap(): array
    {
        $keys = $this->keys();

        return $keys->combine(array_pad([], $keys->count(), Relationship::class))->all();
    }

    /**
     * Objects that are safe to cull.
     *
     * @return array
     */
    protected function cullableObjects(): array
    {
        return $this->keys()->all();
    }
}
