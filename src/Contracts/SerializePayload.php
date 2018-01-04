<?php

namespace DialInno\Jaal\Contracts;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;

interface SerializePayload extends Arrayable, Jsonable, JsonSerializable
{
    //
}
