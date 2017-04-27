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
        ];
    }
}
