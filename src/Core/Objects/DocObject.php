<?php

namespace DialInno\Jaal\Core\Objects;

use DialInno\Jaal\Core\Api\JsonApi;
use Illuminate\Support\Collection;

/**
 * Responsible for serializing a document and preparing a response.
 */
class DocObject extends MetaObject {

    const DOC_NONE = 0;
    const DOC_ONE = 1;
    const DOC_MANY = 2;
    const DOC_ONE_IDENT = 3;
    const DOC_MANY_IDENT = 4;
    const DOC_TYPE_MAX = 4;

    protected $errors;

    protected $json_api = null;

    protected $code = 200;

    protected $doc_type;

    protected $data;
    protected $links;
    protected $included;
    protected $meta;

    public function __construct(JsonApi $json_api, int $doc_type = 0)
    {
        $this->data = new Collection;
        $this->doc_type = $doc_type;
        $this->errors = new Collection;
        $this->json_api = $json_api;
        $this->meta = new Collection;
    }

    public function getHttpStatus()
    {
        if($this->errors->count())
            return $this->errors->reduce(function ($carry, $item) {
                //prime the system
                if($carry === null)
                    return $item->getStatus();

                //if we have different errors, send a 400
                if($item->getStatus() !== $carry)
                    return '400';

                return $item->getStatus();
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

    public function errorCount()
    {
        return $this->errors->count();
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
        if($this->isIdent())
            $resource = new ResourceIdentifierObject($this, $data);
        else
            $resource = new ResourceObject($this, $data);

        //add to data
        $this->data->push($resource);
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

    public function isOne()
    {
        return ($this->doc_type === self::DOC_ONE) || ($this->doc_type === self::DOC_ONE_IDENT);
    }

    public function isMany()
    {
        return ($this->doc_type === self::DOC_MANY) || ($this->doc_type === self::DOC_MANY_IDENT);
    }

    public function isIdent()
    {
        return ($this->doc_type === self::DOC_ONE_IDENT) || ($this->doc_type === self::DOC_MANY_IDENT);
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
        $error_arr = [];
        $data_arr = [];

        if($this->isMany())
        {
            $data_arr = $this->data->jsonSerialize();
        }
        else if($this->isOne())
        {
            if($this->data->count())
                $data_arr = $this->data[0]->jsonSerialize();
            else
                $data_arr = null;
        }

        //todo: toplevel meta object
        if($this->meta->count())
            $out['meta'] = $this->meta->jsonSerialize();

        //if we have errors, ignore the data
        if($this->errors->count())
        {
            $out['errors'] = $this->errors->jsonSerialize();
        }
        //we don't have errors, display the data
        else
        {
            if($this->isMany() || $this->isOne())
                $out['data'] = $data_arr;
        }

        //todo: links

        //todo: included

        return $out;
    }
}
