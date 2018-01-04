<?php

namespace DialInno\Jaal\Objects;

use Illuminate\Support\Collection;

use DialInno\Jaal\Contracts\DeserializePayload;
use DialInno\Jaal\Contracts\SerializePayload;
use DialInno\Jaal\Exceptions\KeyException;
use DialInno\Jaal\Exceptions\ValueException;

abstract class BaseObject implements SerializePayload, DeserializePayload
{
    use Concerns\DeserializesPayload,
        Concerns\SerializesPayload;

    public $payload = null;

    public function __construct()
    {
        $this->payload = Collection::make();
    }
}
