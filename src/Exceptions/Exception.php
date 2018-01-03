<?php

namespace DialInno\Jaal\Exceptions;

use Illuminate\Contracts\Support\Responsable;
use DialInno\Jaal\Objects\Document;

abstract class Exception extends \Exception implements Responsable
{
    protected $response_document = null;

    public function __construct()
    {
        $this->response_document = new Document;
    }

    public function throwIfErrors()
    {
        if($this->response_document->hasErrors()) {
            throw $this;
        }

        return $this;
    }

    /**
     * Attempt to normalize the items into a collection of quoted strings.
     *
     * @param mixed $items
     * @return Illuminate\Support\Collection
     */
    protected function normalize($items)
    {
        $items = collect($items)->map(function ($item, $index) {
            if ($item instanceof \stdClass) {
                return 'Object';
            } else if(is_array($item)) {
                return 'Array';
            } else if (is_null($item)) {
                return 'NULL';
            } else {
                return strval($item);
            }
        });

        return $items;
    }

    /**
     * Convert this exception to a JSON response.
     */
    public function toResponse($request)
    {
        return $this->response_document->toResponse($request);
    }
}
