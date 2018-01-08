<?php

namespace DialInno\Jaal\Objects\Concerns;

use Illuminate\Support\Collection;
use Illuminate\Container\Container;
use DialInno\Jaal\Contracts\Response;

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
     * it along to save a copy in this class and unpack any child classes.
     *
     * @param mixed $payload
     * @param array|null $path
     *
     * @return static
     */
    public static function deserialize($payload, ?array $path = null)
    {
        // if it is already this class, no need to do anything
        if ($payload instanceof static) {
            return $payload;
        }

        $jaal_ex = Container::getInstance()->make(Response::class);

        // if it is a string, attempt to decode it as JSON before doing anything
        if (is_string($payload)) {
            $payload = json_decode($payload, false, 256, JSON_BIGINT_AS_STRING);

            // check if we had a parse error
            if (JSON_ERROR_NONE !== json_last_error()) {
                $jaal_ex
                    ->cannotDeserializeJson()
                    ->throwResponse();
            }
        }

        $path = $path ?? [];

        // we expect stdClass or a Collection
        if (is_object($payload) && get_class($payload) === 'stdClass') {
            $payload = static::make($payload);
        } else if (!($payload instanceof static)) {
            $jaal_ex
                ->unexpectedValue('object', gettype($payload))
                ->throwResponse();
        }

        // create a new instance, set the payload
        $that = new static($payload);

        // assert that its specifications are met
        $that->assertKeysAreToSpec($jaal_ex, $path);
        $that->assertValuesAreToSpec($jaal_ex, $path);

        // if they exist, deserialize the child objects
        $that->deserializeChildren($path);

        // do whatever cleanup needs done before returning the object
        $that->finishedDeserializing($path);

        return $that;
    }

    /**
     * Assert that the payload's immediate keys are to spec.
     *
     * @param Response $jaal_ex
     * @param array $path
     */
    protected function assertKeysAreToSpec(Response $jaal_ex, array $path)
    {
        $keys = $this->keys();


        // if we have a list, they must always be included
        if (method_exists($this, 'payloadMustContain')) {
            $must_contain = $this->payloadMustContain();
            if (count($must_contain)) {
                $must_contain = Collection::make($must_contain);

                // an empty set will not add errors
                $jaal_ex->requiredKey($must_contain->diff($keys));
            }
        }

        // if we have a list, they are the only valid keys
        if (method_exists($this, 'payloadMayContain')) {
            $may_contain = $this->payloadMayContain();
            if (count($may_contain)) {
                // an empty set will not add errors
                $jaal_ex->disallowedKey($keys->diff($may_contain));
            }
        }

        // if we have a list, one of them must be included
        if (method_exists($this, 'payloadMustContainOne')) {
            $must_contain_one = $this->payloadMustContainOne();
            if (count($must_contain_one) && !$keys->intersect($must_contain_one)->count()) {
                $jaal_ex->requireOneKey($must_contain_one);
            }
        }

        // these are the possible key conflicts
        if (method_exists($this, 'payloadConflicts')) {
            $conflicts = $this->payloadConflicts();
            if (count($conflicts)) {
                $conflicts = Collection::make($conflicts);

                $conflicts->each(function ($conflict, $i) use ($keys, $jaal_ex) {
                    if ($keys->intersect($conflict)->count() > 1) {
                        $jaal_ex->conflictingKeys($conflict);
                    }
                });
            }
        }

        $jaal_ex->throwResponseIfErrors();
    }

    /**
     * Assert that the payload's immediate values are to spec.
     *
     * @param Response $jaal_ex
     * @param array $path
     */
    protected function assertValuesAreToSpec(Response $jaal_ex, array $path)
    {
        // check the data types if applicable
        if (method_exists($this, 'payloadDatatypes')) {
            $payload_datatypes = $this->payloadDatatypes();
            if (count($payload_datatypes)) {
                $payload_datatypes = Collection::make($payload_datatypes);

                $payload_datatypes->each(function ($allowed, $key) use ($jaal_ex) {
                    if ($this->has($key)) {
                        $value_type = gettype($this->get($key));

                        if (!in_array($value_type, explode('|', $allowed))) {
                            $jaal_ex->unexpectedValue($allowed, $value_type);
                        }
                    }
                });

                $jaal_ex->throwResponseIfErrors();
            }
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
        if (method_exists($this, 'payloadObjectMap')) {
            $object_map = $this->payloadObjectMap();
            foreach ($object_map as $key => $child_class) {
                $child_payload = $this->get($key);
                $child_path = array_merge($path, [$key]);

                if (is_object($child_payload)) {
                    $this[$key] = $child_class::deserialize($child_payload, $child_path);
                } else if (is_array($child_payload)) {
                    // we assume it is an array of the children
                    // they should have whitelisted this as an array in order for
                    // us to get here
                    $temp = [];

                    foreach ($child_payload as $i => $sub_payload) {
                        $sub_path = array_merge($child_path, [$i]);
                        $temp[] = $child_class::deserialize($sub_payload, $sub_path);
                    }

                    $this[$key] = $temp;
                }
            }
        }
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
