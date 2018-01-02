<?php

namespace DialInno\Jaal\DocObjects;

use JsonSerializable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Collection;
use DialInno\Jaal\Exceptions\KeyException;
use DialInno\Jaal\Exceptions\ValueException;

abstract class DocObject implements Arrayable, Jsonable, JsonSerializable
{
    // this is a collection which represents the payload data
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
                    if ($subitem instanceof DocObject) {
                        $tmp[] = $subitem->toArray();
                    } else {
                        $tmp[] = $subitem;
                    }
                }

                return $tmp;
            } else if ($item instanceof DocObject) {
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

    /**
     * Creates a new DocObject from a JSON payload.
     *
     * We expect that any DocObject is going to be unpacked from a stdClass
     * object or a Collection, but we collect it into a Collection for
     * consistency.
     *
     * We check the keys and values to ensure they are structured as close to
     * spec as possible.
     *
     * @param mixed $payload
     * @param array $path
     *
     * @return DocObject
     */
    public static function unpack($payload, array $path = [])
    {
        if (is_object($payload) && get_class($payload) === 'stdClass') {
            $payload = Collection::make($payload);
        } else if (!($payload instanceof Collection)) {
            throw ValueException::make($path)->expected('object', gettype($payload));
        }

        $that = new static;
        $keys = $payload->keys();

        $key_ex = KeyException::make($path);

        // these are required
        $key_ex->required($that->payloadMustContain()->diff($keys));

        // if we have a list, then they're the only ones allowed
        $may = $that->payloadMayContain();
        if ($may->count()) {
            $key_ex->disallowed($keys->diff($may));
        }

        // we must have one of the following
        $must_one = $that->payloadMustContainOne();
        if ($must_one->count() && !$keys->intersect($must_one)->count()) {
            $key_ex->requireOne($must_one);
        }

        // these are the possible key conflicts
        $that->payloadConflicts()->each(function ($conflict, $i) use ($keys, $key_ex) {
            if ($keys->contains($conflict)) {
                $key_ex->conflicts($conflict);
            }
        });

        $key_ex->throwIfErrors();

        $val_ex = ValueException::make($path);

        // check the data types if applicable
        $that->payloadDatatypes()->each(function ($allowed, $key) use ($payload, $val_ex) {
            if ($payload->has($key)) {
                $value_type = gettype($payload->get($key));

                if (!in_array($value_type, explode('|', $allowed))) {
                    $val_ex->expected($allowed, $value_type);
                }
            }
        });

        $val_ex->throwIfErrors();

        // the payload is structurally correct, so go ahead and unpack it
        $that->unpackPayload($payload, $path);

        return $that;
    }

    /**
     * Each type of object must unpack its payload from a collection.
     *
     * @param Collection $payload
     * @param array $path
     *
     * @return DocObject
     */
    public function unpackPayload(Collection $payload, array $path = [])
    {
        $this->payload = $payload;

        return $this;
    }

    /**
     * If a key exists, unpack it into the supplied object, set an appropriate path, and put it back into the payload.
     *
     * @param string $key
     * @param string $doc_object
     * @param array $path
     */
    public function unpackObject(string $key, string $doc_object, array $path = [])
    {
        if ($this->payload->has($key)) {
            $path[] = $key;

            $this->payload[$key] = $doc_object::unpack($this->payload->get($key), $path);
        }
    }

    /**
     * Like unpack object, except handles an array of objects.
     *
     * @param string $key
     * @param string $doc_object
     * @param array $path
     */
    public function unpackObjectArray(string $key, string $doc_object, array $path = [])
    {
        if ($this->payload->has($key)) {
            $path[] = $key;
            $temp = [];

            foreach ($this->payload->get($key) as $index => $obj) {
                $tmp_path = array_merge($path, [strval($index)]);
                $temp[] = $doc_object::unpack($obj, $tmp_path);
            }

            $this->payload[$key] = $temp;;
        }
    }

    /**
     * Return a collection of keys; the object must contain them all.
     *
     * @return Collection
     */
    protected function payloadMustContain()
    {
        return Collection::make();
    }

    /**
     * Return a collection of keys; they object must contain at least one.
     *
     * @return Collection
     */
    protected function payloadMustContainOne()
    {
        return Collection::make();
    }

    /**
     * Return a collection of keys; this is an extensive list of key names.
     *
     * @return Collection
     */
    protected function payloadMayContain()
    {
        return Collection::make();
    }

    /**
     * Return a collection of key collections; the object can contain one from each list.
     *
     * @return Collection
     */
    protected function payloadConflicts()
    {
        return Collection::make();
    }

    /**
     * Return a collection containing key value pairs of keys and the types that we expect as values.
     *
     * Separate options with a pipe. Acceptable values are here: http://php.net/manual/en/function.gettype.php
     *
     * @return Collection
     */
    protected function payloadDatatypes()
    {
        return Collection::make();
    }
}
