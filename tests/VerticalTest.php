<?php

namespace DialInno\Jaal\Tests;

// THE SYSTEM SHOULD:
// - 
class VerticalTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        
        $this->disableExceptionHandling();
    }

    // -------------------------------------------------------------------------
    // DATA DESERIALIZATION
    // -------------------------------------------------------------------------

    public function testShowAccountNotFound()
    {
        $this->callHttp('GET', '/api/v1/account/1', '');
    }
}
