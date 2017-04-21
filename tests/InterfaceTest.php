<?php

namespace DialInno\Jaal\Tests;

use DialInno\Jaal\JsonApiRoute;
use DialInno\Jaal\Tests\Api\JsonApiV1;
use DialInno\Jaal\Tests\Models\User;
use DialInno\Jaal\Tests\Models\Skill;
use DialInno\Jaal\Tests\Models\Post;

class InterfaceTest extends TestCase
{

    /**
     * Should be able to list and show users via JsonApi.
     *
     * @test
     */
    public function testShowsUsers()
    {
        $users = factory(User::class, 25)->create();

        //get the first 15
        $ja = new JsonApiV1();
        $response = $ja->setModels(['user'])->index()->getResponse();
        $json = $this->contentAsObject($response);

        //assert that we have the first 15 results
        $this->assertCount(15, $json->data);

        //get index 5 and compare
        $ja = new JsonApiV1();
        $response = $ja->setModels(['user'])->setModelIds([5])
            ->show()
            ->getResponse();
        $json = $this->contentAsObject($response);

        //we should have user 5 (index 4 in users array)
        $this->assertEquals($users[5-1]->email, $json->data->attributes->email);
        //password isn't included in our response
        $this->assertObjectNotHasAttribute('password', $json->data->attributes);
    }
}
