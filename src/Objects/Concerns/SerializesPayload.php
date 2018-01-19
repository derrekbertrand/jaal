<?php

namespace DialInno\Jaal\Objects\Concerns;

use DialInno\Jaal\Contracts\Response;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Container\Container;
use Illuminate\Support\Collection;

trait SerializesPayload
{
    /**
     * Convert this into an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->filter(function ($item, $key) {
            $cullable = method_exists($this, 'cullableObjects') ? $this->cullableObjects() : [];

            // if it isn't cullable or it has no content
            if (in_array($key, $cullable) && $item instanceof Collection && !count($item)) {
                return false;
            }

            return true;
        })
        ->map(function ($item, $key) {
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
     * Convert this into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Convert this to its JSON representation.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        $json = json_encode($this->jsonSerialize(), $options);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw app(Response::class)
                ->cannotSerializeJson();
        }

        return $json;
    }
}
