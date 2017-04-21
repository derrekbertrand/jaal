<?php

namespace DialInno\Jaal;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use DialInno\Jaal\Errors\ValidationError;
use DialInno\Jaal\Errors\AuthorizationError;

abstract class JsonApiRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $base_rules = [
            'jsonapi.version' => 'numeric|min:1|max:1.9',
            'data' => 'required|array',
            'data.type' => 'required',
            'data.attributes' => 'required|array'
        ];

        //combine the rules into one master list
        if(method_exists($this, 'dataRules'))
            foreach($this->container->call([$this, 'dataRules']) as $k => $v)
                $base_rules['data.attributes.'.$k] = $v;

        return $base_rules;
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        $base_attributes = [
            'jsonapi.version' => 'JSON API version',
            'data' => 'Data Object',
            'data.type' => 'Data Object Type',
            'data.attributes' => 'Data Object Attributes'
        ];

        //combine the rules into one master list
        if(method_exists($this, 'dataAttributes'))
            foreach($this->container->call([$this, 'dataAttributes']) as $k => $v)
                $base_attributes['data.attributes.'.$k] = $v;

        return $base_attributes;
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exception\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        $json_api = $this->getJsonApi();

        //add all errors from error bag
        foreach($validator->errors()->toArray() as $dot_path => $err)
            foreach($err as $error_text)
                $json_api->getSerializer()->addError(new ValidationError($dot_path, $error_text));

        throw new HttpResponseException($json_api->getResponse());
    }

    /**
     * Handle a failed authorization attempt.
     *
     * @return void
     *
     * @throws \Illuminate\Http\Exception\HttpResponseException
     */
    protected function failedAuthorization()
    {
        $json_api = $this->getJsonApi();

        $json_api->getSerializer()->addError(new AuthorizationError());

        throw new HttpResponseException($json_api->getResponse());
    }

    /**
     * Get the JsonApi instance to use for this request.
     *
     * @return JsonApi
     */
    abstract protected function getJsonApi();
}
