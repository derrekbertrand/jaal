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
    public function store(UserRequest $request)
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

    public function showPosts()
    {
        return $this->json_api->inferQueryParam($this)
            ->showToMany('posts')
            ->getDoc()
            ->getResponse();
    }
}
