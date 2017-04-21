<?php

namespace DialInno\Jaal;

use DialInno\Jaal\Errors\DatabaseError;
use DialInno\Jaal\Errors\Error as JsonApiError;
use DialInno\Jaal\Errors\ResourceNotFoundError;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

class Serializer implements Jsonable, \JsonSerializable
{
    /**
     * The HTTP status code to return.
     */
    protected $code = 200;

    /**
     * The data to serialize into JSON.
     */
    protected $serial = [];

    /**
     * The objects to serialize in data.
     */
    protected $data = null;

    /**
     * The list of errors to serialize.
     */
    protected $errors = [];

    /**
     * The config data from our files.
     */
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;

        $this->serial['jsonapi'] = ['version' => '1.0'];
        $this->serial['meta'] = $config['meta'];
    }

    public function addDataCollection(Collection $collect, bool $is_resource_id = false)
    {
        //it will actually be an array for this request
        if($this->data === null)
            $this->data = [];

        if(!$this->errorCount())
            foreach($collect as $model)
            {
                if(!($model instanceof \JsonSerializable))
                    throw new \BadMethodCallException(get_class($model).' does not implement JsonSerializable.');

                $this->data[] = $model->jsonSerialize();
            }

        return $this;
    }

    public function addDataObject(\JsonSerializable $model = null, bool $is_resource_id = false)
    {
        if(!$this->errorCount())
            if($model !== null)
                $this->data = $model->jsonSerialize();

        return $this;
    }

    public function getResponse()
    {
        return response($this->toJson(), $this->code)
            ->header('Content-Type', 'application/vnd.api+json');
    }

    public function getCode()
    {
        return $this->code;
    }

    /**
     * Add a JsonApiError to our object.
     *
     * @param JsonApiError $e The error to serialize.
     * @return Serializer
     */
    public function addError(JsonApiError $e)
    {
        $this->errors[] = $e;

        $this->code = $e->getHttpCode();

        return $this;
    }

    /**
     * Get the number of errors we have.
     *
     * @return int
     */
    public function errorCount()
    {
        return count($this->errors);
    }

    /**
     * Convert the model instance to JSON.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $out = $this->serial;

        //serialize all errors
        $errors = [];
        foreach($this->errors as $err)
            $errors[] = $err->jsonSerialize();

        //if we have an error add that to the output
        if(count($errors))
            $out['errors'] = $errors;
        //if we don't have errors
        else
            $out['data'] = $this->data;

        return $out;
    }
}
