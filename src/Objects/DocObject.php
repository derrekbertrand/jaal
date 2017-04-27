<?php

namespace DialInno\Jaal\Objects;

use DialInno\Jaal\JsonApi;
use Illuminate\Support\Collection;

/**
 * Responsible for serializing a document and preparing a response.
 */
class DocObject extends MetaObject {

    protected $errors;

    protected $json_api = null;

    protected $code = 200;

    protected $doc_type = 0;
    protected $is_many = false;
    protected $data;
    protected $links;
    protected $included;

    public function __construct(JsonApi $json_api)
    {
        $this->json_api = $json_api;
        $this->doc_type = 0;
        $this->errors = new Collection;
        $this->data = new Collection;
    }

    public function getHttpStatus()
    {
        if($this->errors->count())
            return $this->errors->reduce(function ($carry, $item) {
                //prime the system
                if($carry === null)
                    return $item->getHttpStatus();

                //if we have different errors, send a 400
                if($item->getHttpStatus() !== $carry)
                    return '400';

                return $item->getHttpStatus();
            });
        else
            return $this->code;
    }

    /**
     * I am the document.
     *
     * @return DocObject
     */
    public function getDoc()
    {
        return $this;
    }

     /**
     * Friendly wrapper to add an error.
     *
     * @param  array|Collection|JsonSerializable|ErrorObject $error_data
     * @return ErrorObject
     */
    public function addError($error_data)
    {
        if(!($error_data instanceof ErrorObject))
            $error_data = new ErrorObject($this->getDoc(), $error_data);

        $this->errors->push($error_data);

        return $error_data;
    }

    /**
     * Add data to the document.
     *
     * Keep in mind that it is serialized later.
     *
     * @param  Object  $data
     */
    public function addData($data)
    {
        $resource = new ResourceObject($this, $data);

        //add to data
        $this->data->push($resource);
    }

    public function setOne()
    {
        $this->is_many = false;
    }

    public function setMany()
    {
        $this->is_many = true;
    }

    /**
     * Get a response object; takes json options.
     *
     * @param  int  $options
     * @return Response
     */
    public function getResponse($options = 0)
    {
        $out = $this->toJson($options);

        return response($out, intval($this->getHttpStatus()));
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
     * Helper function to serialize the data portion of the document.
     *
     * @return null|array
     */
    public function serializeData()
    {
        if($this->is_many)
        {
            return $this->data->jsonSerialize();
        }
        else
        {
            if($this->data->count())
                return $this->data[0]->jsonSerialize();
            else
                return null;
        }
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        //todo: serialize everything, have it validate, then check errors

        //create a blank object to serialize
        $out = new Collection;

        $out['jsonapi'] = ['version' => '1.0'];

        $data_arr = $this->serializeData();

        //todo: toplevel meta object

        //if we have errors, ignore the data
        if($this->errors->count())
        {
            $out['errors'] = $this->errors->jsonSerialize();
        }
        //we don't have errors, display the data
        else
        {
            $out['data'] = $data_arr;
        }

        //todo: links

        //todo: included

        return $out;
    }
}
