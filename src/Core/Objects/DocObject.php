<?php

namespace DialInno\Jaal\Core\Objects;

use Illuminate\Support\Collection;
use DialInno\Jaal\Core\Api\JsonApi;
use Illuminate\Support\Facades\Validator;
use DialInno\Jaal\Core\Objects\ErrorObject;
use DialInno\Jaal\Core\Objects\GenericObject;
use DialInno\Jaal\Core\Objects\ResourceObject;
use DialInno\Jaal\Core\Objects\ResourceIdentifierObject;

/**
 * Responsible for serializing a document and preparing a response.
 */
class DocObject extends GenericObject
{

    /**
     * constants to describe the document data
     **/
    const DOC_NONE = 0;
    const DOC_ONE = 1;
    const DOC_MANY = 2;
    const DOC_ONE_IDENT = 3;
    const DOC_MANY_IDENT = 4;
    const DOC_TYPE_MAX = 4;
    /**
     * Document errors
     * @var Illuminate\Database\Eloquent\Collection $errors
     **/
    protected $errors;
    /**
     * The api class in question
     *
     * @var DialInno\Jaal\Core\Api\JsonApi $api
     **/
    protected $api = null;

    /**
     * Default status of 200
     * @var int $code
     **/
    protected $code = 200;
    /**
     * the Document type
     * @var int $doc_type
     **/
    protected $doc_type;

    /**
     * The document data
     * @var Illuminate\Database\Eloquent\Collection $data
     **/
    protected $data;

    /**
     * The document links
     * @var ?
     **/
    protected $links;
    /**
     * The document included data
     * @var ?
     **/
    protected $included;
    /**
     * The document meta
     * @var Illuminate\Database\Eloquent\Collection $data
     **/
    protected $meta;

    public function __construct(JsonApi $json_api, int $doc_type = 0)
    {
        $this->data = new Collection;
        $this->doc_type = $doc_type;
        $this->errors = new Collection;
        $this->json_api = $json_api;
        $this->meta = new Collection;
    }

    /**
     * Get document http status
     * @var string code
     **/
    public function getHttpStatus()
    {
        if ($this->errors->count()) {
            return $this->errors->reduce(function ($carry, $item) {
                //prime the system
                if ($carry === null) {
                    return $item->getStatus();
                }

                //if we have different errors, send a 400
                if ($item->getStatus() !== $carry) {
                    return '400';
                }

                return $item->getStatus();
            });
        } else {
            return $this->code;
        }
    }

    /**
     * I am the document.
     *
     * @return this
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
        if (!($error_data instanceof ErrorObject)) {
            $error_data = new ErrorObject($this->getDoc(), $error_data);
        }

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
        if ($this->isIdent()) {
            $resource = new ResourceIdentifierObject($this, $data);
        } else {
            $resource = new ResourceObject($this, $data);
        }

        //add to data
        $this->data->push($resource);
    }

    /**
     * Validate the api request
     * @param  $array $rules
     * @param  $array $attributes
     */
    protected function validate($rules = null, $messages, $attributes)
    {
        $body = json_decode(request()->getContent(), true) ?: request()->all();
        $validator = Validator::make($body, $rules, $messages, $attributes);

        foreach($validator->errors()->toArray() as $ref => $messages)
            foreach($messages as $message)
                $this->addError(new ValidationErrorObject($this, ['detail' => $message, 'source' => ['pointer' => '/'.str_replace('.', '/', $ref)]]));
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

        return response($out, intval($this->getHttpStatus()))->header('Content-Type', 'application/vnd.api+json');
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
        if ($this->isMany()) {
            $data_arr = $this->data->jsonSerialize();
        } elseif ($this->isOne()) {
            if ($this->data->count()) {
                $data_arr = $this->data[0]->jsonSerialize();
            } else {
                $data_arr = null;
            }
        }

        //todo: toplevel meta object
        if ($this->meta->count()) {

            $out['meta'] = $this->meta->jsonSerialize();
        }

        //if we have errors, ignore the data
        if ($this->errors->count()) {
            $out['errors'] = $this->errors->jsonSerialize();
        }
        //we don't have errors, display the data
        else {
            if ($this->isMany() || $this->isOne()) {
                $out['data'] = $data_arr;
            }
        }

        //todo: links

        //todo: included

        return $out;
    }
}
