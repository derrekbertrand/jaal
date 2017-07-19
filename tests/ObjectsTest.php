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
    public function setUp()
    {
        parent::setUp();

        $this->jsonapi = new JsonApiV1;
        $this->doc = $this->jsonapi->getDoc();
        
    }
    public function test_404_response()
    {
        

        $this->doc->addError(new NotFoundErrorObject($this->doc));

        $response = $this->jsonapi->getDoc()->getResponse();

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertContains('The resource could not be found.', $response->getContent());
    }

    public function test_custom_400_response()
    {
        

        $this->doc->addError([]);

        $response = $this->jsonapi->getDoc()->getResponse();

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertContains('An error occurred.', $response->getContent());
    }

    public function test_custom_500_response()
    {
        

        $this->doc->addError([
            'status' => '500',
            'detail' => 'The system failed unexpectedly.',
        ]);

        $response = $this->jsonapi->getDoc()->getResponse();

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertContains('The system failed unexpectedly.', $response->getContent());
    }

    public function testFallback400Error()
    {
        

        $this->doc->addError([
            'status' => '500',
            'detail' => 'The system failed unexpectedly.',
        ]);

        $this->doc->addError(new NotFoundErrorObject($this->doc));

        $response = $this->jsonapi->getDoc()->getResponse();

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertContains('The system failed unexpectedly.', $response->getContent());
        $this->assertContains('The resource could not be found.', $response->getContent());
    }

    public function test_default_200_response()
    {
        $this->assertEquals(200, $this->doc->getHttpStatus());
    }

    public function test_bad_resource()
    {
        $this->doc->addData(['foo' => 'bar']);

        $response = $this->jsonapi->getDoc()->getResponse();

        $responseContent = $response->getContent();

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertContains('ID is required for a resource object.', $responseContent);
        $this->assertContains('foo is not a valid member of a resource object.', $responseContent);
        $this->assertContains('Type is required for a resource object.', $responseContent);
    }

    public function test_failed_validation_response()
    {

        $this->doc->validate(['foo'=>'required']);

        $response = $this->jsonapi->getDoc()->getResponse();

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertContains('The foo field is required.', $response->getContent());
    }


    public function test_add_collection_error()
    {
        

        $this->doc->addError(new \Illuminate\Support\Collection);

        $response = $this->jsonapi->getDoc()->getResponse();


        $this->assertEquals(400, $response->getStatusCode());
        $this->assertContains('An error occurred.', $response->getContent());
    }

    public function test_add_bad_object()
    {
        

        $this->expectException(\Exception::class);

        $this->doc->addError(new \Exception());
    }

    public function test_bad_member_name_underscore()
    {
        

        $this->expectException(\Exception::class);

        $this->doc->addError(['_foo' => 'bar']);
    }

    public function test_bad_member_name_period()
    {
        

        $this->expectException(\Exception::class);

        $this->doc->addError(['foo.bar' => 'baz']);
    }
}
