<?php

namespace DialInno\Jaal\Tests;

use DialInno\Jaal\JsonApiRoute;
use DialInno\Jaal\Tests\Models\User;
use DialInno\Jaal\Tests\Models\Skill;
use DialInno\Jaal\Tests\Models\Post;

class HttpTest extends TestCase
{

    public function testUserShowNotFound()
    {
        //no user, should throw an error
        $this->json('GET', '/api/v1/user/5')
            ->assertResponseStatus(404)
            ->seeJsonStructure([
                'errors' => [
                    '*' => [
                        'status',
                        'title',
                        'detail',
                    ]
                ]
            ]);
    }

    public function testUserIndexEmptySet()
    {
        //we should get a valid response
        $this->json('GET', '/api/v1/user')
            ->assertResponseStatus(200)
            ->seeJson([
                'jsonapi' => [
                    'version' => '1.0'
                ],
                'data' => []
            ]);
    }

    public function testUserStore()
    {
        // good job!
        $this->json('POST', '/api/v1/user', [
            'data' => [
                'type' => 'user',
                'attributes' => [
                    'first_name' => 'Richard',
                    'last_name' => 'Stallman',
                    'email' => 'rms@example.com'
                ],
            ]
        ])
        ->assertResponseStatus(200);
    }
}
