<?php

namespace DialInno\Jaal\Tests;

use DialInno\Jaal\Objects\Document;
use DialInno\Jaal\Response;

// THE SYSTEM SHOULD:
// - 
class SchemaHydrateTest extends TestCase
{
    use ValidationExamples;

    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        \Orchestra\Testbench\TestCase::setUp();
    }

    /**
     * @dataProvider validationBadSingleDataProvider
     */
    public function testValidationBadExampleCases($schema, $status, $method, $contains, $doesnt_contain, $payload)
    {
        try {
            // deserialize; should have no issues
            $doc = Document::deserialize($payload);

            // instantiate this schema
            $schema = $this->app->make($schema);

            // hydrate a class, but it should fail
            $schema->hydrate($doc->payload->get('data', null), $method, ['data']);

            throw new \Exception('Failed to abort.');
        } catch (Response $e) {
            $res = $e->toResponse(null);

            // the status should be thus, probably 400 or 422
            $this->assertEquals($status, $res->status());

            // it should contain these strings
            foreach($contains as $content) {
                $this->assertContains($content, $res->getContent());
            }

            // it should not contain these strings
            foreach($doesnt_contain as $content) {
                $this->assertNotContains($content, $res->getContent());
            }
        }
    }

    /**
     * @dataProvider validationGoodSingleDataProvider
     */
    public function testValidationGoodExampleCases($schema, $method, $attr_contains, $attr_cannot_contain, $payload)
    {
        $this->disableExceptionHandling();

        // deserialize; should have no issues
        $doc = Document::deserialize($payload);

        // instantiate this schema
        $schema = $this->app->make($schema);

        // hydrate a class, but it should fail
        $res = $schema->hydrate($doc->payload->get('data', null), $method, ['data']);

        // the attributes should be thus
        foreach($attr_contains as $key => $value) {
            $this->assertContains($value, $res->$key);
        }

        // the attributes should not be thus
        foreach($attr_cannot_contain as $key => $value) {
            $this->assertNotContains($value, $res->$key);
        }
    }
}
