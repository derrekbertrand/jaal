<?php

namespace DialInno\Jaal\Objects\Concerns;

use Illuminate\Support\Collection;
use DialInno\Jaal\Exceptions\KeyException;
use DialInno\Jaal\Exceptions\ValueException;

trait DeserializesPayload
{
    /**
     * Creates a new BaseObject from a JSON payload.
     *
     * We expect that any BaseObject is going to be unpacked from a stdClass
     * object or a Collection, but we collect it into a Collection for
     * consistency.
     *
     * We check the keys and values to ensure they are structured as close to
     * spec as possible. We aren't validating the payload, only making sure it
     * follows the structure of the spec.
     *
     * @param mixed $payload
     * @param array $path
     *
     * @return static
     */
    public static function deserialize($payload, ?array $path)
    {
        // if it is a string, attempt to decode it as JSON before doing anything
        if (is_string($payload)) {
            $payload = json_decode($payload, false, 256, JSON_BIGINT_AS_STRING);

            // check if we had a parse error
            if (JSON_ERROR_NONE !== json_last_error()) {
                throw ValueException::make()->expected('object', 'garbage payload');
            }
        }

        // we expect stdClass or a Collection
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
     * Each type of object must deserialize its payload from a collection.
     *
     * @param Collection $payload
     * @param array $path
     *
     * @return BaseObject
     */
    public function deserializePayload(Collection $payload, array $path = [])
    {
        $this->payload = $payload;

        // by default, we don't do anything with it

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
