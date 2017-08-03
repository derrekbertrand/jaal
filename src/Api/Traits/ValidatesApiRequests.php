<?php

namespace DialInno\Jaal\Api\Traits;

use Illuminate\Http\Request;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Contracts\Validation\Validator;
use DialInno\Jaal\Api\DummyApi;
use DialInno\Jaal\Objects\DocObject;
use DialInno\Jaal\Objects\Errors\ValidationErrorObject;

trait ValidatesApiRequests
{
    /**
     * Run the validation routine against the given validator.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @param  \Illuminate\Http\Request|null  $request
     * @param  \DialInno\Jaal\Core\Objects\DocObject  $doc
     *
     * @return mixed
     */
    public function validateApiWith(Validator $validator, Request $request = null, DocObject $doc = null)
    {
        $request = $request ?: request();

        if (is_array($validator)) {
            $validator = $this->getValidationFactory()->make(json_decode($request->getContent(), true), $validator);
        }

        if ($validator->fails()) {
            return $this->serializeErrorDoc($validator, $doc);
        }

        return null;
    }

    /**
     * Validate the given request with the given rules.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $rules
     * @param  array  $messages
     * @param  array  $customAttributes
     * @param  \DialInno\Jaal\Core\Objects\DocObject  $doc
     *
     * @return mixed
     */
    public function validateApi(Request $request, array $rules, array $messages = [], array $customAttributes = [], DocObject $doc = null)
    {
        $validator = $this->getValidationFactory()->make(json_decode($request->getContent(), true), $rules, $messages, $customAttributes);

        if ($validator->fails()) {
            return $this->serializeErrorDoc($validator, $doc);
        }

        return null;
    }

    /**
     * Get a validation factory instance.
     *
     * @return \Illuminate\Contracts\Validation\Factory
     */
    protected function getValidationFactory()
    {
        return app(Factory::class);
    }

    /**
     * Serialize validator errors into a DocObject
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @param  \DialInno\Jaal\Core\Objects\DocObject  $doc
     *
     * @return \Illuminate\Contracts\Validation\Factory
     */
    protected function serializeErrorDoc(Validator $validator, DocObject $doc = null)
    {
        if($doc === null)
        {
            $api = new DummyApi();
            $doc = $api->getDoc();
        }

        foreach($validator->errors()->toArray() as $ref => $messages)
            foreach($messages as $message)
                $doc->addError(new ValidationErrorObject($doc, ['detail' => $message, 'source' => ['pointer' => '/'.str_replace('.', '/', $ref)]]));

        return $doc;
    }
}
