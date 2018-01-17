<?php

namespace DialInno\Jaal\Objects;

use Illuminate\Support\Collection;
use DialInno\Jaal\Contracts\DeserializePayload;
use DialInno\Jaal\Contracts\SerializePayload;
use DialInno\Jaal\Contracts\Response;

abstract class BaseObject extends Collection implements SerializePayload, DeserializePayload
{
    use Concerns\DeserializesPayload,
        Concerns\SerializesPayload;
}
