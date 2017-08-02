<?php

namespace DialInno\Jaal\Tests;

use DialInno\Jaal\Tests\TestCase;
use DialInno\Jaal\Tests\Api\BadApi;
use DialInno\Jaal\Exceptions\UndefinedApiPropertiesException;



class ApiClassTest extends TestCase
{


    public function test_exception_is_thrown_if_required_class_properties_are_missing()
    {

        $this->expectException(UndefinedApiPropertiesException::class);

        new BadApi;
    }
    
}