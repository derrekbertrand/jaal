<?php

namespace DialInno\Jaal\Exceptions;

class QueryParameterException extends Exception
{
    public static function make()
    {
        return new static();
    }

    public function invalid($params)
    {
        $params = $this->normalize($params);

        foreach($params as $param) {
            $this->response_document->addError([
                'status' => '400',
                'title' => 'Invalid Query Parameter',
                'detail' => $param.' is not a valid query parameter.',
                'source' => collect(['parameter' => $param]),
            ]);
        }

        return $this;
    }

    public function reserved($params)
    {
        $params = $this->normalize($params);

        foreach($params as $param) {
            $this->response_document->addError([
                'status' => '400',
                'title' => 'Reserved Query Parameter',
                'detail' => $param.' is a reserved query parameter.',
                'source' => collect(['parameter' => $param]),
            ]);
        }

        return $this;
    }
}
