<?php

namespace DialInno\Jaal\Errors;

class DatabaseError extends Error
{
    protected $http_code = 422;
    protected $code = 'database';
    protected $title = 'The request failed a database constraint.';
    protected $source = [];
}
