<?php

namespace DialInno\Jaal;

use Exception;
use DialInno\Jaal\Contracts\Response;

abstract class Schema
{
    use Concerns\Dehydrates,
        Concerns\Hydrates;

    protected $data;
    protected $path = [];
    protected $method = '';
    protected $exception;
    public static $resource_type;
    public static $model;
    public static $exposed_key = 'id';
    public static $sort_whitelist = [];
    public static $default_limit = 20;
    public static $min_limit = 10;
    public static $max_limit = 50;

    public function __construct()
    {
        $this->exception = app(Response::class);

        if (!is_string(static::$resource_type)) {
            throw new Exception(get_class($this).' must define "public static $resource_type;" as string.');
        }

        if (!is_string(static::$model)) {
            throw new Exception(get_class($this).' must define "public static $model;" as string.');
        }
    }

    /**
     * Return a map of relation names to Schema classes.
     *
     * This is also our whitelist of Schemas, and as a safe default, none are
     * permitted.
     *
     * @return array
     */
    public static function relationshipSchemas(): array
    {
        return [];
    }
}
