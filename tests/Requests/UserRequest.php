<?php

namespace DialInno\Jaal\Tests\Requests;

use DialInno\Jaal\JsonApiRequest;
use DialInno\Jaal\JsonApi;

class UserRequest extends JsonApiRequest
{
    public function authorize()
    {
        return true;
    }

    public function getJsonApi()
    {
        return new JsonApi('v1', ['user']);
    }

    public function rules()
    {
        return [
        ];
    }
}
