<?php

namespace DialInno\Jaal\Contracts;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;

interface SerializePayload extends Arrayable, Jsonable, JsonSerializable
{
    /**
     * Convert the payload into an array.
     *
     * @return array
     */
    public function toArray();

    /**
     * Convert the payload into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize();

    /**
     * Convert the payload to its JSON representation.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0);
}
