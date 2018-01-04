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
     * With all of the basic structural checks passing, we can comfortably pass
     * it along to deserializePayload() to save a copy in this class and unpack
     * any child classes.
     *
     * @param mixed $payload
     * @param array|null $path
     *
     * @return static
     */
    public static function deserialize($payload, ?array $path = null)
    {
        // if it is a string, attempt to decode it as JSON before doing anything
        if (is_string($payload)) {
            $payload = json_decode($payload, false, 256, JSON_BIGINT_AS_STRING);

            // check if we had a parse error
            if (JSON_ERROR_NONE !== json_last_error()) {
                throw JsonException::make()->expected('object', 'garbage payload');
            }
        }

        $path = $path ?? [];

        // we expect stdClass or a Collection
        if (is_object($payload) && get_class($payload) === 'stdClass') {
            $payload = Collection::make($payload);
        } else if (!($payload instanceof Collection)) {
            throw ValueException::make($path)->expected('object', gettype($payload));
        }

        // create a new instance, set the payload
        $that = new static;
        $that->payload = $payload;

        // assert that its specifications are met
        $that->assertKeysAreToSpec($path);
        $that->assertValuesAreToSpec($path);

        // if they exist, deserialize the child objects
        $that->deserializeChildren($path);

        // do whatever cleanup needs done before returning the object
        $that->finishedDeserializing($path);

        return $that;
    }

    /**
     * Assert that the payload's immediate keys are to spec.
     *
     * @param array $path
     */
    protected function assertKeysAreToSpec(array $path)
    {
        $keys = $this->payload->keys();
        $key_ex = KeyException::make($path);

        $must_contain = $this->payloadMustContain();
        $may_contain = $this->payloadMayContain();
        $must_contain_one = $this->payloadMustContainOne();
        $conflicts = $this->payloadConflicts();

        // if we have a list, they must always be included
        if (count($must_contain)) {
            $must_contain = Collection::make($must_contain);

            // an empty set will not add errors
            $key_ex->required($must_contain->diff($keys));
        }

        // if we have a list, they are the only valid keys
        if (count($may_contain)) {
            // an empty set will not add errors
            $key_ex->disallowed($keys->diff($may_contain));
        }

        // if we have a list, one of them must be included
        if (count($must_contain_one) && !$keys->intersect($must_contain_one)->count()) {
            $key_ex->requireOne($must_contain_one);
        }

        // these are the possible key conflicts
        if (count($conflicts)) {
            $conflicts = Collection::make($conflicts);

            $conflicts->each(function ($conflict, $i) use ($keys, $key_ex) {
                if ($keys->contains($conflict)) {
                    $key_ex->conflicts($conflict);
                }
            });
        }

        $key_ex->throwIfErrors();
    }

    /**
     * Assert that the payload's immediate values are to spec.
     *
     * @param array $path
     */
    protected function assertValuesAreToSpec(array $path)
    {
        $payload_datatypes = $this->payloadDatatypes();

        // check the data types if applicable
        if (count($payload_datatypes)) {
            $val_ex = ValueException::make($path);
            $payload_datatypes = Collection::make($payload_datatypes);

            $payload_datatypes->each(function ($allowed, $key) use ($val_ex) {
                if ($this->payload->has($key)) {
                    $value_type = gettype($this->payload->get($key));

                    if (!in_array($value_type, explode('|', $allowed))) {
                        $val_ex->expected($allowed, $value_type);
                    }
                }
            });

            $val_ex->throwIfErrors();
        }
    }


    /**
     * Deserialize the whitelisted child objects.
     *
     * This takes a map of keys to object names and deserializes them. They
     * should have had their datatypes validated by now, so whether it is an
     * object or an array of objects we can safely deserialize them.
     *
     * @param array $path
     */
    protected function deserializeChildren(array $path)
    {
        $object_map = $this->payloadObjectMap();

        foreach ($object_map as $key => $child_class) {
            $child_payload = $this->payload->get($key);
            $child_path = array_merge($path, [$key]);

            if (is_object($child_payload)) {
                $this->payload[$key] = $child_class::deserialize($child_payload, $child_path);
            } else if (is_array($child_payload)) {
                // we assume it is an array of the children
                // they should have whitelisted this as an array in order for
                // us to get here
                $temp = [];

                foreach ($child_payload as $i => $sub_payload) {
                    $sub_path = array_merge($child_path, [$i]);
                    $temp[] = $child_class::deserialize($sub_payload, $sub_path);
                }

                $this->payload[$key] = $temp;
            }
        }
    }

    /**
     * Return a array of keys; the object must contain them all.
     *
     * @return array
     */
    protected function payloadMustContain(): array
    {
        return [];
    }

    /**
     * Return a array of keys; they object must contain at least one.
     *
     * @return array
     */
    protected function payloadMustContainOne(): array
    {
        return [];
    }

    /**
     * Return a array of keys; this is an extensive list of key names.
     *
     * @return array
     */
    protected function payloadMayContain(): array
    {
        return [];
    }

    /**
     * Return a array of key arrays; the object can contain one from each list.
     *
     * @return array
     */
    protected function payloadConflicts(): array
    {
        return [];
    }

    /**
     * Return a array containing key value pairs of keys and the types that we expect as values.
     *
     * Separate options with a pipe. Acceptable values are here: http://php.net/manual/en/function.gettype.php
     *
     * @return array
     */
    protected function payloadDatatypes(): array
    {
        return [];
    }

    /**
     * Return a map of keys to object type.
     *
     * @return array
     */
    protected function payloadObjectMap(): array
    {
        return [];
    }

    /**
     * Do any cleanup before passing this back.
     *
     * @param array $path
     */
    protected function finishedDeserializing(array $path)
    {
        //
    }
}
