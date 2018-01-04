<?php

namespace DialInno\Jaal\Exceptions;

class ValueException extends Exception
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

    public function expected(string $expect, string $received = null)
    {
        $detail = 'Expected value of type '.$expect;

        if ($received !== null) {
            $detail .= ', found '.$received.' instead';
        }

        $detail .= '.';

        $this->response_document->addError([
            'status' => '400',
            'title' => 'Expected Value',
            'detail' => $detail,
            'source' => collect(['pointer' => $this->path]),
        ]);

        return $this;
    }

    public function unsupportedResource(string $type)
    {
        $this->response_document->addError([
            'status' => '400',
            'title' => 'Unsupported Resource Type',
            'detail' => $type.' is not a supported resource type.',
            'source' => collect(['pointer' => $this->path]),
        ]);
    }
}
