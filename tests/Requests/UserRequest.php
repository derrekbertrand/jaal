<?php

namespace DialInno\Jaal\Tests\Requests;

use DialInno\Jaal\JsonApiRequest;
use DialInno\Jaal\Tests\Api\JsonApiV1;

class UserRequest extends JsonApiRequest
{
    public function authorize()
    {
        return true;
    }

    public function getJsonApi()
    {
        return new JsonApiV1();
    }

    public function dataRules()
    {
        return [
            'email' => 'required|email|max:127',
            'first_name' => 'required|max:127',
            'last_name' => 'required|max:127'
        ];
    }

    public function dataAttributes()
    {
        return [
            'email' => 'E-Mail',
            'first_name' => 'Given Name',
            'last_name' => 'Surname'
        ];
    }
}
