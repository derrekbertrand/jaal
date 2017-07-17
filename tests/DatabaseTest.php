<?php

namespace DialInno\Jaal\Tests;

use DialInno\Jaal\JsonApiRoute;
use DialInno\Jaal\Tests\Models\User;
use DialInno\Jaal\Tests\Models\Skill;
use DialInno\Jaal\Tests\Models\Post;

/**
 * Database relationship testing suite.
 *
 * We make sure that the migrations have been run and Eloquent operates as
 * intended. With these tests out of the way, we know that Laravel is
 * functioning as intended in this test suite. We can shift worry to our
 * own code.
 */
class DatabaseTest extends TestCase
{

    /**
     * Test User and Post relationships.
     *
     * @test
     */
    public function testUserPostRelationships()
    {
        $users = factory(User::class, 25)->create();
        $u = factory(User::class)->create();

        factory(Post::class, 25)->create(['user_id' => $users[0]->id]);
        $p = factory(Post::class)->create(['user_id' => $u->id]);

        //assert that they are related both ways
        $this->assertEquals($u->id, $p->op->id);
        $this->assertEquals($p->id, $u->posts()->first()->id);

        //assert that the other user has 25 posts, and u has 1
        $this->assertEquals(25, $users[0]->posts()->count());
        $this->assertEquals(1, $u->posts()->count());

        //assert that if the post is deleted, the user remains
        $u->posts()->delete();
        $this->assertEquals(
            26,
            User::count()
        );

        //assert that if the user is deleted, the posts go too
        $users[0]->delete();
        $this->assertEquals(
            25,
            User::count()
        );
        $this->assertEquals(
            0,
            Post::count()
        );
    }

    /**
     * Test User and Skill relationships.
     *
     * @test
     */
    public function testUserSkillRelationships()
    {
        $users = factory(User::class, 25)->create();

        $skills = factory(Skill::class, 25)->create();

        //all users have skill 0
        $skills[0]->users()->sync($users);
        $this->assertEquals(
            25,
            $skills[0]->users()->count()
        );

        //user 0 has all skills
        $users[0]->skills()->sync($skills);
        $this->assertEquals(
            25,
            $users[0]->skills()->count()
        );

        //our pivot table should have 49 rows (made 50 but one is a repeat)
        $this->assertEquals(
            49,
            \DB::select('SELECT count() as count FROM skill_user')[0]->count
        );

        //deleting user 1 should only reduce it by 1
        $users[1]->delete();
        $this->assertEquals(
            48,
            \DB::select('SELECT count() as count FROM skill_user')[0]->count
        );

        //deleting user 0 should reduce it back to 23
        $users[0]->delete();
        $this->assertEquals(
            23,
            \DB::select('SELECT count() as count FROM skill_user')[0]->count
        );

        //unlike posts, deleting users will not affect skills
        User::truncate();
        $this->assertEquals(
            25,
            Skill::count()
        );
    }
}
