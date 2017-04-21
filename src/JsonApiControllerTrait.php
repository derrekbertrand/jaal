<?php

namespace DialInno\Jaal;

/**
 * Provide default for read and delete actions.
 *
 * Usually, reading and deleting operations don't need any special treatment.
 * Most of the time, you are doing the same thing and there is no validation
 * to do other than it existing (which Jaal takes care of).
 * Permissions can be granted in the constructor or routes, so 90% of the time
 * you can just include this trait and clean up your controller.
 *
 * It expects that you define $json_api as a class property.
 */
trait JsonApiControllerTrait
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(!isset($this->json_api) || is_null($this->json_api))
            throw new \Exception('Controller must define `protected $json_api;` to use JsonApiControllerTrait.');

        return $this->json_api
            ->inferQueryParam($this)
            ->index()
            ->getResponse();
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        if(!isset($this->json_api) || is_null($this->json_api))
            throw new \Exception('Controller must define `protected $json_api;` to use JsonApiControllerTrait.');

        return $this->json_api
            ->inferQueryParam($this)
            ->show()
            ->getResponse();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        if(!isset($this->json_api) || is_null($this->json_api))
            throw new \Exception('Controller must define `protected $json_api;` to use JsonApiControllerTrait.');

        return $this->json_api
            ->inferQueryParam($this)
            ->destroy()
            ->getResponse();
    }
}
