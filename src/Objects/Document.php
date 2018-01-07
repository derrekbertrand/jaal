<?php

namespace DialInno\Jaal\Objects;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Collection;

class Document extends BaseObject implements Responsable
{
    protected $http_status = 200;

    /**
     * Create a new error from properties.
     *
     * @param mixed $error
     *
     * @return Document
     */
    public function addError($error)
    {
        $errors = $this->payload->get('errors', []);
        $error = Collection::make($error);
        $errors[] = $error;
        $error_status = intval($error->get('status', '400'));

        $this->changeStatus($error_status);

        $this->payload->put('errors', $errors);

        return $this;
    }

    public function changeStatus(int $new_status)
    {
        // we have two kinds of status codes
        // we'll either set a generic status or override the new status
        if ($this->http_status !== $new_status) {
            if ($this->http_status >= 500 || $new_status >= 500) {
                // if either one is a 5XX, that takes precedence
                $this->http_status = 500;
            } else if ($this->http_status >= 400 && $new_status >= 400) {
                // if they're both 4XX, set a generic
                $this->http_status = 400;
            } else if ($this->http_status < 400) {
                // http_status is non-error, so override it 
                $this->http_status = $new_status;
            }
        }
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
     * Return a array of keys; they object must contain at least one.
     *
     * @return array
     */
    protected function payloadMustContainOne(): array
    {
        return ['data', 'errors', 'meta'];
    }

    /**
     * Return a array of keys; this is an extensive list of key names.
     *
     * @return array
     */
    protected function payloadMayContain(): array
    {
        return ['data', 'errors', 'meta', 'jsonapi', 'links', 'included'];
    }

    /**
     * Return a array of key arrays; the object can contain one from each list.
     *
     * @return array
     */
    protected function payloadConflicts(): array
    {
        return [['data', 'errors']];
    }

    /**
     * Return a array containing key value pairs of keys and the types that we expect as values.
     *
     * @return array
     */
    protected function payloadDatatypes(): array
    {
        return [
            'data' => 'NULL|array|object',
            'errors' => 'array',
            'meta' => 'object',
            'jsonapi' => 'object',
            'links' => 'object',
            'included' => 'array',
        ];
    }

    /**
     * Return a map of keys to object type.
     *
     * @return array
     */
    protected function payloadObjectMap(): array
    {
        return [
            'data' => Resource::class,
            'errors' => Error::class,
            'meta' => Meta::class,
            'jsonapi' => JsonApi::class,
            'links' => Link::class,
            'included' => Resource::class,
        ];
    }

    /**
     * Do any cleanup before passing this back.
     *
     * @param array $path
     */
    protected function finishedDeserializing(array $path)
    {
        foreach ($this->payload->get('errors', []) as $error) {
            $this->changeStatus(intval($error->payload->get('status', 400)));
        }
    }

    /**
     * Convert this object to a JSON response.
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
