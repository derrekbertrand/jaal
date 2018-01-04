<?php

namespace DialInno\Jaal\Contracts;

interface DeserializePayload
{
    /**
     * This object can deserialize a payload and return a new object.
     *
     * @param mixed $payload
     * @param array|null $path
     *
     * @return static
     */
    public static function deserialize($payload, ?array $path);
}
