<?php

namespace DialInno\Jaal\Tests;

use DialInno\Jaal\JsonApiRoute;
use DialInno\Jaal\Tests\Models\User;
use DialInno\Jaal\Tests\Models\Skill;
use DialInno\Jaal\Tests\Models\Post;

class HttpTest extends TestCase
{

    public function testUserEndpoints()
    {
        //we should get a valid response
        $this->get('/api/v1/user')
            ->seeJson([
                'jsonapi' => [
                    'version' => '1.0'
                ],
                'data' => []
            ]);

        //we should get a 400 for not structuring our data correctly
        $this->json('POST', '/api/v1/user', ['poop' => 'pee'])
            ->assertResponseStatus(400)
            ->seeJsonStructure([
                'errors' => [
                    '*' => [
                        'status',
                        'title',
                        'detail'
                    ],
                ],
            ]);

        //if not there, we must send a 404 back
        $this->get('/api/v1/user/5')
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

        //we should get a 400 for not structuring our data correctly
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
