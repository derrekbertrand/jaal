<?php

namespace DialInno\Jaal\Tests;

use DialInno\Jaal\Objects\Document;
use DialInno\Jaal\Response;

// THE SYSTEM SHOULD:
// - deserialize JSON into Objects
// - validate the structure of the JSON document
// - throw Responsable Exceptions
// - contain relevant error information
class DocumentDeserializeTest extends TestCase
{
    use Data\StandardExamples;
    use Data\BadExamples;

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
        $res = $this->app->make(Document::class)->deserialize($payload)->toResponse(null);

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
            $this->app->make(Document::class)->deserialize($payload);

            throw new \Exception('Failed to abort.');
        } catch (\DialInno\Jaal\Response $e) {
            $res = $e->toResponse(null);

            $this->assertEquals($status, $res->status());
            $this->assertContains($contains, $res->getContent());
        }
    }

    // this is about the only sensible scenario I could think of
    public function testBadServerSerialization()
    {

        $doc = $this->app->make(Document::class)->deserialize('{"meta": {"foo": "bar"}}');
        $doc['meta'] = tmpfile();

        try {
            $doc->toResponse(null);
        } catch (\DialInno\Jaal\Response $e) {
            $res = $e->toResponse(null);

            $this->assertEquals(500, $res->status());
            $this->assertContains('Internal Serialization Error', $res->getContent());
        }

        fclose($doc->get('meta'));
    }
}
