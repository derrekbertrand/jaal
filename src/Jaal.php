<?php

namespace DialInno\Jaal;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Route;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

use DialInno\Jaal\Objects\Document;
use DialInno\Jaal\Contracts\Jaal as JaalContract;

abstract class Jaal implements JaalContract
{
    use Concerns\DefinesRoutes;

    protected $request = null;
    protected $request_doc = null;
    protected $request_type = null;

    protected $response_doc = null;

    protected $path_model_id = null;
    protected $path_model_type = null;
    protected $path_model_relation = null;
    protected $path_resource_offset = 2;

    public function __construct()
    {
        // $this->request = $request;
        // $this->response_doc = new Document;
    }

    public static function fromRequest(Request $request)
    {
        $that = new static($request);

        $that->inferPathInfo();
        $that->inferRequestType();

        // unpack the content
        if ($that->request_type === 'store' || $that->request_type === 'update') {
            $that->request_doc = Document::unpack($request->getContent());
        }

        return $that;
    }

    public function inferRequestType()
    {
        $method = $this->request->method();

        if ($method === 'POST') {
            $this->request_type = 'store';
        } else if ($method === 'PATCH') {
            $this->request_type = 'update';
        } else if ($method === 'DELETE') {
            $this->request_type = 'destroy';
        } else if ($method === 'GET') {
            if ($this->path_model_id !== null) {
                $this->request_type = 'show';
            } else {
                $this->request_type = 'index';
            }
        } else {
            throw \Exception('Unknown HTTP verb.');
        }
    }

    public function inferPathInfo()
    {
        $params = $this->request->route()->parameters();
        $route = explode('.',\Route::currentRouteName());

        // set the id to the query param or null
        $this->path_model_id = array_pop($params);

        // set type or throw an exception
        $type = $route[$this->path_resource_offset];
        if (!array_key_exists($type, static::$resources)) {
            throw new \Exception('This API does not support that resource type.');
        }
        $this->path_model_type = $type;

        // if we have an id, we can also check for the relationship via the path
        if ($this->path_model_id !== null) {
            ; // grab that later
        }

        return $this;
    }

    public function handle()
    {
        return $this->{$this->request_type}();
    }

    public function show()
    {
        $resource = $this->getResource();

        $this->response_doc = $resource::showById($this->path_model_id, $this->getIncludeList(), $this->getFieldList());

        return $this;
    }

    public function getModel()
    {
        return static::$resources[$this->path_model_type]['model'];
    }

    public function getResource()
    {
        return static::$resources[$this->path_model_type]['resource'];
    }

    public function getIncludeList()
    {
        $query = $this->request->query->all();

        if (array_key_exists('include', $query)) {
            ;
        } else {
            return [];
        }
    }

    public function getFieldList()
    {
        $query = $this->request->query->all();

        if (array_key_exists('fields', $query)) {
            ;
        } else {
            return [];
        }
    }
}
