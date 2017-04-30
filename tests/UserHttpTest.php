<?php

namespace DialInno\Jaal\Tests;

use DialInno\Jaal\JsonApiRoute;
use DialInno\Jaal\Tests\Models\User;
use DialInno\Jaal\Tests\Models\Skill;
use DialInno\Jaal\Tests\Models\Post;

class UserHttpTest extends TestCase
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

    public function testUserStoreValidationErrors()
    {
        $this->json('POST', '/api/v1/user', [
                'data' => [
                    'type' => 'user',
                    'attributes' => [
                        'first' => 'Richard',
                        'last' => 'Stallman',
                        'email' => 'rmsexample.com'
                    ],
                ]
            ])
            ->assertResponseStatus(400);
    }

    public function testUserDelete()
    {
        $u = factory(User::class)->create();

        $response = $this->json('DELETE', '/api/v1/user/1')
            ->assertResponseStatus(200);
    }

    public function testUserDeleteNotFound()
    {
        $response = $this->json('DELETE', '/api/v1/user/1')
            ->assertResponseStatus(404);
    }

    public function testUserUpdate()
    {
        $u = factory(User::class)->create();

        $response = $this->json('PATCH', '/api/v1/user/1', [
                'data' => [
                    'type' => 'user',
                    'attributes' => [
                        'first_name' => 'Richard',
                        'last_name'  => 'Stallman',
                        'email'      => 'rms@example.org',
                    ],
                ],
            ])
            ->assertResponseStatus(200);
    }

    public function testUserUpdateNotFound()
    {
        $this->json('PATCH', '/api/v1/user/1', [
                'data' => [
                    'type' => 'user',
                    'attributes' => [
                        'first_name' => 'Richard',
                        'last_name'  => 'Stallman',
                        'email'      => 'rms@example.org',
                    ],
                ],
            ])
            ->assertResponseStatus(404);
    }

    public function testUserSort()
    {
        User::create([
            'first_name' => 'Alfred',
            'last_name' => 'Pennyworth',
            'email' => 'ap@batcave.org',
            'password' => '',
        ]);
        User::create([
            'first_name' => 'Bruce',
            'last_name' => 'Wayne',
            'email' => 'bw@batcave.org',
            'password' => '',
        ]);
        User::create([
            'first_name' => 'Cassandra',
            'last_name' => 'Wayne',
            'email' => 'cw@batcave.org',
            'password' => '',
        ]);
        User::create([
            'first_name' => 'Dick',
            'last_name' => 'Grayson',
            'email' => 'dg@batcave.org',
            'password' => '',
        ]);

        $response = $this->call('GET', '/api/v1/user', ['sort' => '-last_name,email']);
        $this->assertEquals(200, $response->status());

        $json = $this->contentAsObject($response);

        //this is the appropriate order
        $this->assertEquals(2, $json->data[0]->id);
        $this->assertEquals(3, $json->data[1]->id);
        $this->assertEquals(1, $json->data[2]->id);
        $this->assertEquals(4, $json->data[3]->id);
    }

    public function testUserIndexPostRelationship()
    {
        $u = factory(User::class)->create();

        factory(Post::class, 20)->create(['user_id' => $u->id]);

        $response = dd($this->get('/api/v1/user/1/relationships/posts', ['page' => ['limit' => '25']]));
        $this->assertEquals(200, $response->status());
    }
}
