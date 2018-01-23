<?php

namespace DialInno\Jaal;

use Exception;
use DialInno\Jaal\Objects\Document;
use DialInno\Jaal\Objects\Error;
use DialInno\Jaal\Objects\ErrorSource;
use DialInno\Jaal\Contracts\Response as ResponseContract;

/**
 * A Responsable, fluent, throwable singleton.
 */
class Response extends Exception implements ResponseContract
{
    /**
     * An array of Error objects.
     *
     * @var array
     */
    protected $errors = [];

    public function throwResponse()
    {
        throw $this;
    }

    public function throwResponseIfErrors()
    {
        if($this->hasErrors()) {
            $this->throwResponse();
        }

        return $this;
    }

    public function hasErrors()
    {
        return (bool) count($this->errors);
    }

    public function addError(Error $e)
    {
        $this->errors[] = $e;

        return $this;
    }

    public function clearErrors()
    {
        $this->errors = [];

        return $this;
    }

    protected function pointerSource(...$path)
    {
        $path = collect($path)->flatten()->implode('/');

        return new ErrorSource(['pointer' => '/'.$path]);
    }

    protected function paramSource(...$path)
    {
        $path = collect($path)->flatten()->implode('.');

        return new ErrorSource(['parameter' => $path]);
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
        return $this->addError(new Error([
            'status' => '400',
            'title' => 'Bad Payload',
            'detail' => 'Could not deserialize the payload.',
        ]));
    }

    public function cannotSerializeJson()
    {
        return $this->addError(new Error([
            'status' => '500',
            'title' => 'Internal Serialization Error',
            'detail' => 'The server could not serialize a response.',
        ]));
    }

    public function invalidKey($keys, ...$path)
    {
        $keys = $this->normalize($keys);

        foreach($keys as $key) {
            $this->addError(new Error([
                'status' => '400',
                'title' => 'Invalid Key',
                'detail' => $key.' is not a valid key name.',
                'source' => $this->pointerSource($path, $key),
            ]));
        }

        return $this;
    }

    public function disallowedKey($keys, ...$path)
    {
        $keys = $this->normalize($keys);

        foreach($keys as $key) {
            $this->addError(new Error([
                'status' => '400',
                'title' => 'Disallowed Key',
                'detail' => $key.' is not an allowed key in this context.',
                'source' => $this->pointerSource($path, $key),
            ]));
        }

        return $this;
    }

    public function requiredKey($keys, ...$path)
    {
        $keys = $this->normalize($keys);

        foreach($keys as $key) {
            $this->addError(new Error([
                'status' => '400',
                'title' => 'Required Key',
                'detail' => $key.' is a required key in this context.',
                'source' => $this->pointerSource($path, $key),
            ]));
        }

        return $this;
    }

    public function conflictingKeys($keys, ...$path)
    {
        $keys = $this->normalize($keys);

        return $this->addError(new Error([
            'status' => '400',
            'title' => 'Key Conflict',
            'detail' => 'Cannot have keys together in this context: '.$keys->implode(', '),
            'source' => $this->pointerSource($path),
        ]));
    }

    public function requireOneKey($keys, ...$path)
    {
        $keys = $this->normalize($keys);

        return $this->addError(new Error([
            'status' => '400',
            'title' => 'Key Missing',
            'detail' => 'The context requires one of the following keys: '.$keys->implode(', '),
            'source' => $this->pointerSource($path),
        ]));
    }

    public function invalidQueryParam($params)
    {
        $params = $this->normalize($params);

        foreach($params as $param) {
            $this->addError(new Error([
                'status' => '400',
                'title' => 'Invalid Query Parameter',
                'detail' => $param.' is not a valid query parameter for this endpoint.',
                'source' => $this->paramSource($param),
            ]));
        }

        return $this;
    }

    public function reservedQueryParam($params)
    {
        $params = $this->normalize($params);

        foreach($params as $param) {
            $this->addError(new Error([
                'status' => '400',
                'title' => 'Reserved Query Parameter',
                'detail' => $param.' is a reserved query parameter.',
                'source' => $this->paramSource($param),
            ]));
        }

        return $this;
    }

    public function invalidValue(string $detail, ...$path)
    {
        return $this->addError(new Error([
            'status' => '422',
            'detail' => $detail,
            'source' => $this->pointerSource($path),
        ]));
    }

    public function unexpectedValue($expect, $received, ...$path)
    {
        $detail = 'Expected '.strval($expect).', found '.strval($received).' instead.';

        return $this->addError(new Error([
            'status' => '400',
            'title' => 'Unexpected Value',
            'detail' => $detail,
            'source' => $this->pointerSource($path),
        ]));
    }

    /**
     * Convert this exception to a JSON response.
     */
    public function toResponse($request)
    {
        $doc = new Document(['errors' => $this->errors]);

        foreach ($doc->get('errors', []) as $error) {
            $doc->changeStatus(intval($error->get('status', 400)));
        }

        return $doc->toResponse($request);
    }
}
