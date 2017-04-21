<?php

namespace DialInno\Jaal\Errors;

class RqlError extends Error
{
    protected $http_code = 400;
    protected $code = 'rql_error';
    protected $title = '';
    protected $detail = '';
    protected $source = [];

    public function __construct(\Exception $e)
    {
        $this->title = 'RQL Error';
        $this->detail = $e->getMessage();
    }
}
