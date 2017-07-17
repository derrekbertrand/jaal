<?php

namespace DialInno\Jaal\Tests\Controllers;

use Illuminate\Http\Request;
use DialInno\Jaal\Tests\Requests\UserRequest;
use DialInno\Jaal\Tests\Api\JsonApiV1;

class UserController extends Controller
{
    public function __construct()
    {
        $this->json_api = new JsonApiV1;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return $this->json_api->inferQueryParam($this)
            ->index()
            ->getDoc()
            ->getResponse();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return $this->json_api->inferQueryParam($this)
            ->store(array_merge($request->all()['data']['attributes'],['password' => '']))
            ->getDoc()
            ->getResponse();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        return $this->json_api->inferQueryParam($this)
            ->show()
            ->getDoc()
            ->getResponse();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UserRequest $request, $id)
    {
        return $this->json_api->inferQueryParam($this)
            ->update()
            ->getDoc()
            ->getResponse();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        return $this->json_api->inferQueryParam($this)
            ->destroy()
            ->getDoc()
            ->getResponse();
    }

    //==================================================================================================================

    public function showPosts()
    {
        return $this->json_api->inferQueryParam($this)
            ->showToMany('posts')
            ->getDoc()
            ->getResponse();
    }

    public function storePosts()
    {
        return $this->json_api->inferQueryParam($this)
            ->storeToMany('posts')
            ->getDoc()
            ->getResponse();
    }

    public function updatePosts()
    {
        return $this->json_api->inferQueryParam($this)
            ->updateToMany('posts')
            ->getDoc()
            ->getResponse();
    }

    public function destroyPosts()
    {
        return $this->json_api->inferQueryParam($this)
            ->destroyToMany('posts')
            ->getDoc()
            ->getResponse();
    }

    //==================================================================================================================

    public function showSkills()
    {
        return $this->json_api->inferQueryParam($this)
            ->showManyToMany('skills')
            ->getDoc()
            ->getResponse();
    }

    public function storeSkills()
    {
        return $this->json_api->inferQueryParam($this)
            ->storeManyToMany('skills')
            ->getDoc()
            ->getResponse();
    }

    public function updateSkills()
    {
        return $this->json_api->inferQueryParam($this)
            ->updateManyToMany('skills')
            ->getDoc()
            ->getResponse();
    }

    public function destroySkills()
    {
        return $this->json_api->inferQueryParam($this)
            ->destroyManyToMany('skills')
            ->getDoc()
            ->getResponse();
    }
}
