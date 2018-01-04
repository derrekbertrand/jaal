<?php

namespace DialInno\Jaal\Tests;

use DialInno\Jaal\Objects\Document;
use DialInno\Jaal\Exceptions\ValidationException;

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
    public function testValidationBadExampleCases($schema, $method, $payload)
    {
        try {
            // deserialize; should have no issues
            $doc = Document::deserialize($payload);

            // instantiate this schema
            $schema = new $schema;

            // hydrate a class, but it should fail
            $schema->hydrate($doc->payload->get('data', null), $method, ['data']);

            throw new \Exception('Failed to abort.');
        } catch (ValidationException $e) {
            $res = $e->toResponse(null);

            echo $res->getContent()."\n\n";

            $this->assertEquals(422, $res->status());
        }
    }
}
