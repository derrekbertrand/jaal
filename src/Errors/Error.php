<?php

namespace DialInno\Jaal\Errors;

abstract class Error implements \JsonSerializable
{
    protected $http_code = 400;
    protected $code = 'undefined';
    protected $title = 'Undefined';
    protected $detail = '';
    protected $source = [];

    public function getHttpCode()
    {
        return $this->http_code;
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'code' => $this->code,
            'title' => $this->title,
            'detail' => $this->detail,
            'source' => $this->source
        ];
    }
}
