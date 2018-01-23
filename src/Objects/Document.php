<?php

namespace DialInno\Jaal\Objects;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Collection;

class Document extends BaseObject implements Responsable
{
    public $http_status = 200;

    protected $included_resources = [];

    public function includeResource(Resource $resource)
    {
        $uniq_id = $resource->get('type').'/'.$resource->get('id');

        // if we don't have that key, set it
        if (!isset($this->included_resources[$uniq_id])) {
            $this->included_resources[$uniq_id] = $resource;
        }

        return $this;
    }

    public function getIncluded(string $type, string $id)
    {
        $uniq_id = $resource->get('type').'/'.$resource->get('id');

        if (isset($this->included_resources[$uniq_id])) {
            return $this->included_resources[$uniq_id];
        }

        return null;
    }

    public function finalizeIncluded()
    {
        $included = array_values($this->included_resources);

        if (count($included)) {
            $this->put('included', $included);
        }

        $this->included_resources = [];

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
            'links' => Links::class,
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
        // adjust to an appropriate response code
        foreach ($this->get('errors', []) as $error) {
            $this->changeStatus(intval($error->get('status', 400)));
        }
    }

    /**
     * Objects that are safe to cull.
     *
     * @return array
     */
    protected function cullableObjects(): array
    {
        return [
            'meta',
            'links',
            'errors',
            'included',
        ];
    }

    /**
     * Convert this object to a JSON response.
     */
    public function toResponse($request)
    {
        return response(
            $this->toJson(),
            $this->http_status
            // ['X-Person' => $this->name]
        );
    }
}
