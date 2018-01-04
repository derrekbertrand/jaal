<?php

namespace DialInno\Jaal\Contracts;

use Illuminate\Support\Collection;
use DialInno\Jaal\Exceptions\KeyException;
use DialInno\Jaal\Exceptions\ValueException;

interface DeserializePayload
{
    /**
     * This object can deserialize a payload and return a new 
     *
     * @param mixed $payload
     * @param array $path
     *
     * @return static
     */
    public static function deserialize($payload, ?array $path);
}
