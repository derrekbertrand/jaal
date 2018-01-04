<?php

namespace DialInno\Jaal\Contracts;

interface HydratePayload
{
    /**
     * Takes the internal payload, validates and hydrates it.
     *
     * Typically this returns a Model or a ModelCollection.
     *
     * @return mixed
     */
    public function hydrate();
}
