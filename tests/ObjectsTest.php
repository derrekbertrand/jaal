<?php

namespace DialInno\Jaal\Tests;

use DialInno\Jaal\JsonApiRoute;
use DialInno\Jaal\Tests\Models\Post;
use DialInno\Jaal\Tests\Models\User;
use DialInno\Jaal\Tests\Models\Skill;
use DialInno\Jaal\Tests\Api\JsonApiV1;
use DialInno\Jaal\Core\Errors\NotFoundErrorObject;
use DialInno\Jaal\Core\Errors\ValidationErrorObject;


class ObjectsTest extends TestCase
{
    /**
    * @test
    */
    public function test_404_response()
    {
        $jsonapi = new JsonApiV1;
        $doc = $jsonapi->getDoc();

        $doc->addError(new NotFoundErrorObject($doc));

        $response = $jsonapi->getDoc()->getResponse();

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertContains('The resource could not be found.', $response->getContent());
    }

    // public function testCustom400Error()
    // {
    //     $jsonapi = new JsonApiV1;
    //     $doc = $jsonapi->getDoc();

    //     $doc->addError([]);

    //     $response = $jsonapi->getDoc()->getResponse();

    //     $this->assertEquals(400, $response->getStatusCode());
    //     $this->assertContains('An error occurred.', $response->getContent());
    // }

    // public function testCustom500Error()
    // {
    //     $jsonapi = new JsonApiV1;
    //     $doc = $jsonapi->getDoc();

    //     $doc->addError([
    //         'status' => '500',
    //         'detail' => 'The system failed unexpectedly.',
    //     ]);

    //     $response = $jsonapi->getDoc()->getResponse();

    //     $this->assertEquals(500, $response->getStatusCode());
    //     $this->assertContains('The system failed unexpectedly.', $response->getContent());
    // }

    // public function testFallback400Error()
    // {
    //     $jsonapi = new JsonApiV1;
    //     $doc = $jsonapi->getDoc();

    //     $doc->addError([
    //         'status' => '500',
    //         'detail' => 'The system failed unexpectedly.',
    //     ]);

    //     $doc->addError(new NotFoundErrorObject($doc));

    //     $response = $jsonapi->getDoc()->getResponse();

    //     $this->assertEquals(400, $response->getStatusCode());
    //     $this->assertContains('The system failed unexpectedly.', $response->getContent());
    //     $this->assertContains('The resource could not be found.', $response->getContent());
    // }

    // public function test200Default()
    // {
    //     $jsonapi = new JsonApiV1;
    //     $doc = $jsonapi->getDoc();

    //     $this->assertEquals(200, $doc->getHttpStatus());
    // }

    // public function testBadResource()
    // {
    //     $jsonapi = new JsonApiV1;
    //     $doc = $jsonapi->getDoc();

    //     $doc->addData(['foo' => 'bar']);

    //     $response = $jsonapi->getDoc()->getResponse();

    //     $this->assertEquals(500, $response->getStatusCode());
    //     $this->assertContains('ID is required for a resource object.', $response->getContent());
    //     $this->assertContains('foo is not a valid member of a resource object.', $response->getContent());
    //     $this->assertContains('Type is required for a resource object.', $response->getContent());
    // }

    // public function testValidationError()
    // {
    //     $jsonapi = new JsonApiV1;
    //     $doc = $jsonapi->getDoc();

    //     $doc->addError(new ValidationErrorObject($doc, [
    //         'detail' => 'The field is required.'
    //     ]));

    //     $response = $jsonapi->getDoc()->getResponse();

    //     $this->assertEquals(400, $response->getStatusCode());
    //     $this->assertContains('The field is required.', $response->getContent());
    // }

    // public function testAddCollectionObject()
    // {
    //     $jsonapi = new JsonApiV1;
    //     $doc = $jsonapi->getDoc();

    //     $doc->addError(new \Illuminate\Support\Collection);

    //     $response = $jsonapi->getDoc()->getResponse();

    //     $this->assertEquals(400, $response->getStatusCode());
    //     $this->assertContains('An error occurred.', $response->getContent());
    // }

    // public function testAddBadObject()
    // {
    //     $jsonapi = new JsonApiV1;
    //     $doc = $jsonapi->getDoc();

    //     $this->expectException(\Exception::class);

    //     $doc->addError(new \Exception());
    // }

    // public function testBadMemberNameUnderscore()
    // {
    //     $jsonapi = new JsonApiV1;
    //     $doc = $jsonapi->getDoc();

    //     $this->expectException(\Exception::class);

    //     $doc->addError(['_foo' => 'bar']);
    // }

    // public function testBadMemberNamePeriod()
    // {
    //     $jsonapi = new JsonApiV1;
    //     $doc = $jsonapi->getDoc();

    //     $this->expectException(\Exception::class);

    //     $doc->addError(['foo.bar' => 'baz']);
    // }
}
