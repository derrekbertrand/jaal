<?php

namespace DialInno\Jaal\Objects;

use Illuminate\Support\Collection;

use DialInno\Jaal\Contracts\DeserializePayload;
use DialInno\Jaal\Contracts\SerializePayload;
use DialInno\Jaal\Exceptions\KeyException;
use DialInno\Jaal\Exceptions\ValueException;

abstract class BaseObject extends Collection implements SerializePayload, DeserializePayload
{
    use Concerns\DeserializesPayload,
        Concerns\SerializesPayload;
}
