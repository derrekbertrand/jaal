<?php

namespace DialInno\Jaal\Errors;

class AuthorizationError extends Error
{
    protected $http_code = 401;
    protected $code = 'authorization_error';
    protected $title = 'Authorization Error';
    protected $detail = 'You are not authorized to perform this action.';
    protected $source = [];
}
