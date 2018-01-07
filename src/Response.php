<?php

namespace DialInno\Jaal;

use Exception;
use DialInno\Jaal\Objects\Document;
use DialInno\Jaal\Contracts\Response as ResponseContract;

/**
 * A Responsable, fluent, throwable singleton.
 */
class Response extends Exception implements ResponseContract
{
    protected $document = null;

    public function __construct()
    {
        $this->document = new Document;
    }

    public function throwResponse()
    {
        throw $this;
    }

    public function throwResponseIfErrors()
    {
        if($this->document->hasErrors()) {
            $this->throwResponse();
        }

        return $this;
    }

    protected function collapsePointer($pointer = null)
    {
        return '/'.implode('/', $pointer);
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
                return '[Object]';
            } else if(is_array($item)) {
                return '[Array]';
            } else if (is_null($item)) {
                return '[NULL]';
            } else {
                return '\''.strval($item).'\'';
            }
        });

        return $items;
    }

    public function cannotDeserializeJson()
    {
        $this->document->addError([
            'status' => '400',
            'title' => 'Bad Payload',
            'detail' => 'Could not deserialize the payload.',
        ]);

        return $this;
    }

    public function cannotSerializeJson()
    {
        $this->document->addError([
            'status' => '500',
            'title' => 'Internal Serialization Error',
            'detail' => 'The server could not serialize a response.',
        ]);

        return $this;
    }

    public function invalidKey($keys)
    {
        $keys = $this->normalize($keys);

        foreach($keys as $key) {
            $this->document->addError([
                'status' => '400',
                'title' => 'Invalid Key',
                'detail' => $key.' is not a valid key name.',
                'source' => collect(['pointer' => 'BADPOINTER'.'/'.$key]),
            ]);
        }

        return $this;
    }

    public function disallowedKey($keys)
    {
        $keys = $this->normalize($keys);

        foreach($keys as $key) {
            $this->document->addError([
                'status' => '400',
                'title' => 'Disallowed Key',
                'detail' => $key.' is not an allowed key in this context.',
                'source' => collect(['pointer' => 'BADPOINTER'.'/'.$key]),
            ]);
        }

        return $this;
    }

    public function requiredKey($keys)
    {
        $keys = $this->normalize($keys);

        foreach($keys as $key) {
            $this->document->addError([
                'status' => '400',
                'title' => 'Required Key',
                'detail' => $key.' is a required key in this context.',
                'source' => collect(['pointer' => 'BADPOINTER']),
            ]);
        }

        return $this;
    }

    public function conflictingKeys($keys)
    {
        $keys = $this->normalize($keys);

        $this->document->addError([
            'status' => '400',
            'title' => 'Key Conflict',
            'detail' => 'Cannot have keys together in this context: '.$keys->implode(', '),
            'source' => collect(['pointer' => 'BADPOINTER']),
        ]);

        return $this;
    }

    public function requireOneKey($keys)
    {
        $keys = $this->normalize($keys);

        $this->document->addError([
            'status' => '400',
            'title' => 'Key Missing',
            'detail' => 'The context requires one of the following keys: '.$keys->implode(', '),
            'source' => collect(['pointer' => 'BADPOINTER']),
        ]);

        return $this;
    }

    public function invalidQueryParam($params)
    {
        $params = $this->normalize($params);

        foreach($params as $param) {

            $this->document->addError([
                'status' => '400',
                'title' => 'Invalid Query Parameter',
                'detail' => $param.' is not a valid query parameter for this endpoint.',
                'source' => collect(['parameter' => $param]),
            ]);
        }

        return $this;
    }

    public function reservedQueryParam($params)
    {
        $params = $this->normalize($params);

        foreach($params as $param) {
            $this->document->addError([
                'status' => '400',
                'title' => 'Reserved Query Parameter',
                'detail' => $param.' is a reserved query parameter.',
                'source' => collect(['parameter' => $param]),
            ]);
        }

        return $this;
    }

    public function invalidValue(string $detail, array $path)
    {
        $this->document->addError([
            'status' => '422',
            'detail' => $detail,
            'source' => collect(['pointer' => $this->collapsePointer($path)]),
        ]);
    }

    public function unexpectedValue(string $expect, string $received = null)
    {
        $detail = 'Expected value of type '.$expect;

        if ($received !== null) {
            $detail .= ', found '.$received.' instead';
        }

        $detail .= '.';

        $this->document->addError([
            'status' => '400',
            'title' => 'Unexpected Value',
            'detail' => $detail,
            'source' => collect(['pointer' => 'BADPOINTER']),
        ]);

        return $this;
    }

    /**
     * Convert this exception to a JSON response.
     */
    public function toResponse($request)
    {
        return $this->document->toResponse($request);
    }
}
