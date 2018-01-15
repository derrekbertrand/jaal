<?php

namespace DialInno\Jaal;

use Illuminate\Support\Facades\Route;
use DialInno\Jaal\Jaal;

class JaalRouter
{
    /**
     * Generate routes for a given API class.
     **/
    public static function routes(string $jaal)
    {
        $prefix = $jaal::$api_prefix;

        Route::group([
            'prefix' => $prefix,
            'as' => $prefix.'.',
            ], function () use ($jaal) {
                static::defineRoutes($jaal);
        });
    }

    /**
     * Define all specified routes for this group.
     **/
    protected static function defineRoutes(string $jaal)
    {
        // define routes for each resource group with a controller
        foreach ($jaal::$resources as $name => $resource) {
            if (array_key_exists('controller', $resource)) {
                Route::resource($name, '\\'.$resource['controller'], ['except' => ['create', 'edit']]);

                //------------------------------------------------------------------------------------------------------
                // Although these should be defined appropriately, we're not going to worry about relationships links
                // right now. The only thing that relationships endpoints can do that a typical update cannot, is to add
                // or remove some associations on to-many relationships without having to exhaustively list what the
                // related resources should be. You can still easily update to-many relations, you just have to specify
                // every single resource for the relation every time you update it.
                //
                // This will be completed once the library is in a more finalized state.
                //------------------------------------------------------------------------------------------------------

                foreach ($resource['to_one'] ?? [] as $relation) {
                    static::defineRelationRoutes($name, $resource['controller'], $relation);
                }

                foreach ($resource['to_many'] ?? [] as $relation) {
                    static::defineRelationRoutes($name, $resource['controller'], $relation);
                    static::defineManyRelationRoutes($name, $resource['controller'], $relation);
                }
            }
        }
    }

    /**
     * Define the standard relation routes.
     **/
    protected static function defineRelationRoutes(string $name, string $controller, string $relation)
    {
        $studly_relation = studly_case($relation);

        Route::get("$name/{{$name}}/relationships/$relation", [
            'as' => "$name.relationships.$relation.show",
            'uses' => '\\'.$controller.'@show'.$studly_relation,
        ]);

        Route::patch("$name/{{$name}}/relationships/$relation", [
            'as' => "$name.relationships.$relation.update",
            'uses' => '\\'.$controller.'@update'.$studly_relation,
        ]);
    }

    /**
     * Define the additional endpoints required for to-many relations.
     **/
    protected static function defineManyRelationRoutes(string $name, string $controller, string $relation)
    {
        $studly_relation = studly_case($relation);

        Route::post("$name/{{$name}}/relationships/$relation", [
            'as' => "$name.relationships.$relation.store",
            'uses' => '\\'.$controller.'@store'.$studly_relation,
        ]);

        Route::delete("$name/{{$name}}/relationships/$relation", [
            'as' => "$name.relationships.$relation.destroy",
            'uses' => '\\'.$controller.'@destroy'.$studly_relation,
        ]);
    }
}
