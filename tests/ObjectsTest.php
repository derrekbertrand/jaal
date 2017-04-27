<?php

namespace DialInno\Jaal\Tests;

use DialInno\Jaal\JsonApiRoute;
use DialInno\Jaal\Tests\Api\JsonApiV1;
use DialInno\Jaal\Tests\Models\User;
use DialInno\Jaal\Tests\Models\Skill;
use DialInno\Jaal\Tests\Models\Post;
use DialInno\Jaal\Objects\Errors\NotFoundErrorObject;

class ObjectsTest extends TestCase
{
    public function test404Error()
    {
        $jsonapi = new JsonApiV1;
        $doc = $jsonapi->getDoc();

        $doc->addError(new NotFoundErrorObject($doc));

        $response = $jsonapi->getResponse();

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertContains('The resource could not be found.', $response->getContent());
    }

    public function testCustom400Error()
    {
        $jsonapi = new JsonApiV1;
        $doc = $jsonapi->getDoc();

        $doc->addError([]);

        $response = $jsonapi->getResponse();

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertContains('An error occurred.', $response->getContent());
    }

    public function testCustom500Error()
    {
        $jsonapi = new JsonApiV1;
        $doc = $jsonapi->getDoc();

        $doc->addError([
            'status' => '500',
            'detail' => 'The system failed unexpectedly.',
        ]);

        $response = $jsonapi->getResponse();

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertContains('The system failed unexpectedly.', $response->getContent());
    }

    public function testFallback400Error()
    {
        $jsonapi = new JsonApiV1;
        $doc = $jsonapi->getDoc();

        $doc->addError([
            'status' => '500',
            'detail' => 'The system failed unexpectedly.',
        ]);

        $doc->addError(new NotFoundErrorObject($doc));

        $response = $jsonapi->getResponse();

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertContains('The system failed unexpectedly.', $response->getContent());
        $this->assertContains('The resource could not be found.', $response->getContent());
    }

    public function test200Default()
    {
        $jsonapi = new JsonApiV1;
        $doc = $jsonapi->getDoc();

        $this->assertEquals(200, $doc->getHttpStatus());
    }

    public function testBadResource()
    {
        $jsonapi = new JsonApiV1;
        $doc = $jsonapi->getDoc();

        $doc->addData(['foo' => 'bar']);

        $response = $jsonapi->getResponse();

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertContains('The system failed unexpectedly.', $response->getContent());
        $this->assertContains('The resource could not be found.', $response->getContent());
    }
}
