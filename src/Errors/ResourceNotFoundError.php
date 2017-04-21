<?php

namespace DialInno\Jaal\Errors;

class ResourceNotFoundError extends Error
{
    protected $http_code = 404;
    protected $code = 'resource_not_found';
    protected $title = 'The resource you requested does not exist.';
    protected $detail;
    protected $source = [];
}
