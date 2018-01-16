<?php

namespace DialInno\Jaal\Concerns;

use Illuminate\Support\Facades\Route;

trait DefinesRoutes
{
    protected static $route_group_settings = [];
    protected static $route_controllers = [];
    protected static $route_to_one_relations = [];
    protected static $route_to_many_relations = [];


    public static function getRouteGroupSettings(): array
    {
        return static::$route_group_settings;
    }

    public static function getRouteControllers(): array
    {
        return static::$route_controllers;
    }

    public static function getRouteRelations(?string $resource_name = null): array
    {
        if ($resource_name !== null) {
            return array_merge(
                static::getRouteToOneRelations($resource_name),
                static::getRouteToManyRelations($resource_name)
            );
        } else {
            return array_merge(
                static::getRouteToOneRelations(),
                static::getRouteToManyRelations()
            );
        }
    }

    public static function getRouteToOneRelations(?string $resource_name = null): array
    {
        if ($resource_name !== null) {
            return static::$route_to_one_relations[$resource_name] ?? [];
        } else {
            return static::$route_to_one_relations;
        }
    }

    public static function getRouteToManyRelations(?string $resource_name = null): array
    {
        if ($resource_name !== null) {
            return static::$route_to_many_relations[$resource_name] ?? [];
        } else {
            return static::$route_to_many_relations;
        }
    }

    /**
     * Define all specified routes for this group.
     **/
    public static function defineRoutes()
    {
        Route::group(static::getRouteGroupSettings(), function () {
            foreach (static::getRouteControllers() as $resource_name => $controller) {
                Route::resource($resource_name, $controller, ['except' => ['create', 'edit']]);

                foreach (static::getRouteRelations($resource_name) as $relation) {
                    static::defineRelationRoutes($resource_name, $controller, $relation);
                }

                foreach (static::getRouteToManyRelations($resource_name) as $relation) {
                    static::defineToManyRelationRoutes($resource_name, $controller, $relation);
                }
            }
        });
    }

    /**
     * Define the standard relation routes.
     **/
    protected static function defineRelationRoutes(string $name, string $controller, string $relation)
    {
        $studly_relation = studly_case($relation);

        Route::get("$name/{{$name}}/relationships/$relation", [
            'as' => "$name.relationships.$relation.show",
            'uses' => $controller.'@show'.$studly_relation,
        ]);

        Route::patch("$name/{{$name}}/relationships/$relation", [
            'as' => "$name.relationships.$relation.update",
            'uses' => $controller.'@update'.$studly_relation,
        ]);
    }

    /**
     * Define the additional endpoints required for to-many relations.
     **/
    protected static function defineToManyRelationRoutes(string $name, string $controller, string $relation)
    {
        $studly_relation = studly_case($relation);

        Route::post("$name/{{$name}}/relationships/$relation", [
            'as' => "$name.relationships.$relation.store",
            'uses' => $controller.'@store'.$studly_relation,
        ]);

        Route::delete("$name/{{$name}}/relationships/$relation", [
            'as' => "$name.relationships.$relation.destroy",
            'uses' => $controller.'@destroy'.$studly_relation,
        ]);
    }
}
