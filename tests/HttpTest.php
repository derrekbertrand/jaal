<?php

namespace DialInno\Jaal\Tests;

use DialInno\Jaal\JsonApiRoute;
use DialInno\Jaal\Tests\Models\User;
use DialInno\Jaal\Tests\Models\Skill;
use DialInno\Jaal\Tests\Models\Post;

class HttpTest extends TestCase
{

    public function testIndexUser()
    {
        $this->get('/api/v1/user')
            ->seeJson([
                'jsonapi' => [
                    'version' => '1.0'
                ],
                'data' => []
            ]);
    }

    public function testShowUser()
    {
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
    }
}
