<?php

namespace DialInno\Jaal\Objects\Concerns;

use DialInno\Jaal\Contracts\Response;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Container\Container;

trait SerializesPayload
{
    /**
     * Convert the payload into an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->payload->map(function ($item, $key) {
            if (is_array($item)) {
                $tmp = [];

                foreach ($item as $subitem) {
                    if ($subitem instanceof Arrayable) {
                        $tmp[] = $subitem->toArray();
                    } else {
                        $tmp[] = $subitem;
                    }
                }

                return $tmp;
            } else if ($item instanceof Arrayable) {
                return $item->toArray();
            }

            return $item;
        })->all();
    }

    /**
     * Convert the payload into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Convert the payload to its JSON representation.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        $json = json_encode($this->jsonSerialize(), $options);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw Container::getInstance()
                ->make(Response::class)
                ->cannotSerializeJson();
        }

        return $json;
    }
}
