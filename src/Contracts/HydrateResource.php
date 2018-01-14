<?php

namespace DialInno\Jaal\Contracts;

use DialInno\Jaal\Objects\Resource;

interface HydrateResource
{
    /**
     * Takes the internal payload, validates and hydrates it.
     *
     * Typically this returns a Model or a ModelCollection.
     *
     * @return mixed
     */
    public function hydrate(Resource $jaal_resource);

    public function withPath(array $path);

    public function withMethod(string $method);
}
