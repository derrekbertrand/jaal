<?php

namespace DialInno\Jaal\Exceptions;

class JsonException extends Exception
{
    public static function make()
    {
        return new static();
    }

    public function deserialize()
    {
        $this->response_document->addError([
            'status' => '400',
            'title' => 'Bad Payload',
            'detail' => 'Could not deserialize the payload.',
        ]);

        return $this;
    }

    public function serialize()
    {
        $this->response_document->addError([
            'status' => '500',
            'title' => 'Internal Serialization Error',
            'detail' => 'The server could not serialize a response.',
        ]);

        return $this;
    }
}
