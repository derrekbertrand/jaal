<?php

namespace DialInno\Jaal\Errors;

class BadRequestError extends Error
{
    protected $http_code = 400;
    protected $code = 'bad_request';
    protected $title = 'The request was malformed or nonconformant to standards.';
    protected $source = [];
}
