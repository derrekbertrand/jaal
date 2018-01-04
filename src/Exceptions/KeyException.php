<?php

namespace DialInno\Jaal\Exceptions;

class KeyException extends Exception
{
    protected $path = null;

    public function __construct(array $path)
    {
        parent::__construct();

        $this->path = '/'.implode('/', $path);
    }

    public static function make(array $path)
    {
        return new static($path);
    }

    public function invalid($keys)
    {
        $keys = $this->normalize($keys);

        foreach($keys as $key) {
            $this->response_document->addError([
                'status' => '400',
                'title' => 'Invalid Key',
                'detail' => $key.' is not a valid key name.',
                'source' => collect(['pointer' => $this->path.'/'.$key]),
            ]);
        }

        return $this;
    }

    public function disallowed($keys)
    {
        $keys = $this->normalize($keys);

        foreach($keys as $key) {
            $this->response_document->addError([
                'status' => '400',
                'title' => 'Disallowed Key',
                'detail' => $key.' is not an allowed key in this context.',
                'source' => collect(['pointer' => $this->path.'/'.$key]),
            ]);
        }

        return $this;
    }

    public function required($keys)
    {
        $keys = $this->normalize($keys);

        foreach($keys as $key) {
            $this->response_document->addError([
                'status' => '400',
                'title' => 'Required Key',
                'detail' => $key.' is a required key in this context.',
                'source' => collect(['pointer' => $this->path]),
            ]);
        }

        return $this;
    }

    public function conflicts($keys)
    {
        $keys = $this->normalize($keys);

        $this->response_document->addError([
            'status' => '400',
            'title' => 'Key Conflict',
            'detail' => 'Cannot have keys together in this context: '.$keys->implode(', '),
            'source' => collect(['pointer' => $this->path]),
        ]);

        return $this;
    }

    public function requireOne($keys)
    {
        $keys = $this->normalize($keys);

        $this->response_document->addError([
            'status' => '400',
            'title' => 'Key Missing',
            'detail' => 'The context requires one of the following keys: '.$keys->implode(', '),
            'source' => collect(['pointer' => $this->path]),
        ]);

        return $this;
    }
}
