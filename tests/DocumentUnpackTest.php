<?php

namespace DialInno\Jaal\Tests;

use DialInno\Jaal\DocObjects\Top;
use DialInno\Jaal\Exceptions\BadDocumentException;
use DialInno\Jaal\Exceptions\KeyException;
use DialInno\Jaal\Exceptions\ValueException;

// THE SYSTEM SHOULD:
// - unpack JSON into DocObjects
// - validate the structure of the JSON document
// - throw Responsable Exceptions
// - contain relevant error information
class DocumentUnpackTest extends TestCase
{
    use StandardExamples;

    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        \Orchestra\Testbench\TestCase::setUp();
    }

    // -------------------------------------------------------------------------
    // DATA DESERIALIZATION
    // -------------------------------------------------------------------------

    /**
     * @dataProvider standardExampleProvider
     */
    public function testStdExampleCases($status, $payload)
    {
        $res = Top::unpack($payload)->toResponse(null);

        // assert that the content we're getting out is the same as if we just ran
        // the thing through a decode and encode
        // in the future it might be better to pre-decode-encode these strings
        $this->assertEquals($status, $res->status());
        $this->assertEquals(json_encode(json_decode($payload)), $res->getContent());
    }
}
