<?php

namespace DialInno\Jaal\DocObjects;

use DialInno\Jaal\Exceptions\ValueException;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Collection;

class Top extends DocObject implements Responsable
{
    protected $http_status = 200;

    /**
     * Create a new error from properties.
     *
     * @param mixed $error
     *
     * @return Top
     */
    public function addError($error)
    {
        $errors = $this->payload->get('errors', []);
        $error = Error::unpack(Collection::make($error), []);
        $errors[] = $error;
        $error_status = intval($error->payload->get('status', '400'));

        // we have two kinds of errors, so we'll pass pack a generic error code
        if ($this->http_status !== $error_status) {
            if ($this->http_status >= 500 || $error_status >= 500) {
                // if either one is a 5XX, that takes precedence
                $this->http_status = 500;
            } else if ($this->http_status >= 400 && $error_status >= 400) {
                // if they're both 4XX, set a generic
                $this->http_status = 400;
            } else if ($this->http_status < 400) {
                // http_status is non-error, so override it 
                $this->http_status = $error_status;
            }
        }

        $this->payload->put('errors', $errors);

        return $this;
    }

    public function hasErrors()
    {
        return count($this->payload->get('errors', [])) ? true : false;
    }

    /**
     * Determine the status code for this document.
     *
     * @return integer
     */
    public function httpStatus()
    {
        return $this->http_status;
    }

    /**
     * Creates a new toplevel document from a JSON payload.
     *
     * Unlike other DocObjects, if it is a string, it will attempt to parse the
     * payload as JSON before unpacking.
     *
     * @param mixed $payload
     *
     * @return DocObject
     */
    public static function unpack($payload, array $path = [])
    {
        if (is_string($payload)) {
            $payload = json_decode($payload, false, 256, JSON_BIGINT_AS_STRING);

            // check if we had a parse error
            if (JSON_ERROR_NONE !== json_last_error()) {
                throw ValueException::make()->expected('object', 'garbage payload');
            }
        }

        return parent::unpack($payload, $path);
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
        $data = $this->payload->get('data');

        if (is_array($data)) {
            $this->unpackObjectArray('data', Resource::class, $path);
        } else if (is_object($data)) {
            $this->unpackObject('data', Resource::class, $path);
        }

        $this->unpackErrorsArray();
        $this->unpackObject('meta', Meta::class, $path);
        $this->unpackObject('jsonapi', JsonApi::class, $path);
        $this->unpackObject('links', Link::class, $path);
        $this->unpackObjectArray('included', Resource::class, $path);

        return $this;
    }

    /**
     * Like unpack object, except handles an array of objects.
     */
    protected function unpackErrorsArray()
    {
        // addError appends, so forget errors before trying to add them
        $errors = $this->payload->get('errors', []);
        $this->payload->forget('errors');

        // this only actually runs if there are any errors
        foreach ($errors as $index => $obj) {
            $this->addError($obj);
        }
    }

    /**
     * Return a collection of keys; they object must contain at least one.
     *
     * @return Collection
     */
    protected function payloadMustContainOne()
    {
        return Collection::make(['data', 'errors', 'meta']);
    }

    /**
     * Return a collection of keys; this is an extensive list of key names.
     *
     * @return Collection
     */
    protected function payloadMayContain()
    {
        return Collection::make(['data', 'errors', 'meta', 'jsonapi', 'links', 'included']);
    }

    /**
     * Return a collection of key collections; the object can contain one from each list.
     *
     * @return Collection
     */
    protected function payloadConflicts()
    {
        return Collection::make([
            Collection::make(['data', 'errors'])
        ]);
    }

    /**
     * Return a collection containing key value pairs of keys and the types that we expect as values.
     *
     * @return Collection
     */
    protected function payloadDatatypes()
    {
        return Collection::make([
            'data' => 'NULL|array|object',
            'errors' => 'array',
            'meta' => 'object',
            'jsonapi' => 'object',
            'links' => 'object',
            'included' => 'array',
        ]);
    }

    /**
     * Convert this exception to a JSON response.
     */
    public function toResponse($request)
    {
        return response(
            $this->toJson(),
            $this->httpStatus()
            // ['X-Person' => $this->name]
        );
    }
}
