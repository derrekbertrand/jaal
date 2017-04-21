<?php

namespace DialInno\Jaal\Errors;

class ValidationError extends Error
{
    protected $http_code = 422;
    protected $code = 'validation_error';
    protected $title = '';
    protected $detail = '';
    protected $source = [];

    public function __construct(string $dotPath, string $errText)
    {
        $this->title = $dotPath;
        $this->detail = $errText;
        $this->source['pointer'] = '/'.implode('/',explode('.', $dotPath));
    }
}
