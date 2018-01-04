<?php

namespace DialInno\Jaal\Tests;

use DialInno\Jaal\Objects\Document;
use DialInno\Jaal\Exceptions\BadDocumentException;
use DialInno\Jaal\Exceptions\KeyException;
use DialInno\Jaal\Exceptions\ValueException;

// THE SYSTEM SHOULD:
// - unpack JSON into Objects
// - validate the structure of the JSON document
// - throw Responsable Exceptions
// - contain relevant error information
class DocumentUnpackTest extends TestCase
{
    use StandardExamples;
    use BadExamples;

    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        \Orchestra\Testbench\TestCase::setUp();
    }

    /**
     * @dataProvider standardExampleProvider
     */
    public function testStdExampleCases($status, $payload)
    {
        $res = Document::deserialize($payload)->toResponse(null);

        // assert that the content we're getting out is the same as if we just ran
        // the thing through a decode and encode
        // in the future it might be better to pre-decode-encode these strings
        $this->assertEquals($status, $res->status());
        $this->assertEquals(json_encode(json_decode($payload)), $res->getContent());
    }

    /**
     * @dataProvider badExampleProvider
     */
    public function testBadExampleCases($status, $contains, $payload)
    {
        try {
            Document::deserialize($payload);

            throw new \Exception('Failed to abort.');
        } catch (\DialInno\Jaal\Exceptions\Exception $e) {
            $res = $e->toResponse(null);

            $this->assertEquals($status, $res->status());
            $this->assertContains($contains, $res->getContent());
        }
    }
}
