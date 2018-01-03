<?php

namespace DialInno\Jaal\Objects;

use JsonSerializable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Collection;

use DialInno\Jaal\Contracts\DeserializePayload;
use DialInno\Jaal\Exceptions\KeyException;
use DialInno\Jaal\Exceptions\ValueException;

abstract class BaseObject implements Arrayable, DeserializePayload, Jsonable, JsonSerializable
{
    use Concerns\DeserializesPayload;

    public $payload = null;

    public function __construct()
    {
        $this->payload = Collection::make();
    }

    /**
     * Convert the object into an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->payload->map(function ($item, $key) {
            if (is_array($item)) {
                $tmp = [];

                foreach ($item as $subitem) {
                    if ($subitem instanceof BaseObject) {
                        $tmp[] = $subitem->toArray();
                    } else {
                        $tmp[] = $subitem;
                    }
                }

                return $tmp;
            } else if ($item instanceof BaseObject) {
                return $item->toArray();
            }

            return $item;
        });
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        $json = json_encode($this->jsonSerialize(), $options);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \Exception('Failed to serialize JSON');
        }

        return $json;
    }
}
