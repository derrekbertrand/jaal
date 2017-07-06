<?php 

namespace DialInno\Jaal\Core\Api\Traits;

use DialInno\Jaal\Core\Errors\Exceptions\Config\ApiModelsUndefinedException;
use DialInno\Jaal\Core\Errors\Exceptions\Config\ApiRoutesUndefinedException;
use DialInno\Jaal\Core\Errors\Exceptions\Config\ApiVersionUndefinedException;
use DialInno\Jaal\Core\Errors\Exceptions\Config\ApiPropertiesUndefinedException;
use DialInno\Jaal\Core\Errors\Exceptions\Config\ApiRelationshipsUndefinedException;

trait ValidatesApiClasses
{
    /**
     * Checks if a version is defined on the api class
     *  @var string $class
     **/
    protected function isVersionDefined($class)
    {

        if (!property_exists($class, 'version') || empty(static::$version)) {
            return false;
        }
        return true;
    }

    /**
     * Throw an version undefined exception if the version is not defined
     *  @var string $class
     **/
    protected function isVersionDefinedOrFail($class)
    {
        if (!$this->isVersionDefined($class)) {
            throw new ApiVersionUndefinedException("{$class} must define`protected static \$version;`.");
        }
    }

    /**
     * Checks if the api class has its routes defined
     *  @var string $class
     **/
    protected function isRoutesDefined($class)
    {
       
        if (!property_exists($class, 'routes') || empty(static::$routes)) {
            return false;
        }
        return true;
    }

    /**
     * Throw an routes undefined exception if the routes are not defined
     * @var string $classtring $class
     **/
    protected function isRoutesDefinedOrFail($class)
    {   

        if (!$this->isRoutesDefined($class)) {
            throw new ApiRoutesUndefinedException("{$class} must define `protected static \$routes;'.");
        }
    }
    /**
     * Checks if the api class has relationships set
     *  @var string $class
     **/
    protected function isRelationshipsDefined($class)
    {   

        if (!property_exists($class, 'relationships') || empty(static::$relationships)) {
            return false;
        }
        return true;
    }

    /**
     * Throw a relationship undefined exception if the relationships are not defined
     *  @var string $class
     **/
    protected function isRelationshipsDefinedOrFail($class)
    {
        if (!$this->isRoutesDefined($class)) {
            throw new ApiRelationshipsUndefinedException("{$class} must define `protected static \$relationships;`.");
        }
    }
    /**
     * Checks if the api class has properties set
     *  @var string $class
     **/
    protected function isModelsDefined($class)
    {
        if (!property_exists($class, 'models') || empty(static::$models)) {
            return false;
        }
        return true;
    }

    /**
     * Throw a models undefined exception if the models are not defined
     *  @var string $class
     **/
    protected function isModelsDefinedOrFail($class)
    {
        if (!$this->isModelsDefined($class)) {
            throw new ApiModelsUndefinedException("{$class} must define `protected static \$relationships;`.");
        }
    }
    /**
     * Checks  @var string $class if the api class has meta set
     **/
    protected function isMetaDefined($class)
    {
        if (!property_exists($class, 'meta') || empty(static::$meta)) {
            return false;
        }
        return true;
    }

    /**
     * Throw a meta undefined exception if the meta is not defined
     *  @var string $class
     **/
    protected function isMetaDefinedOrFail($class)
    {
        if (!$this->isMetaDefined($class)) {
            throw new ApiMetaUndefinedException("{$class} must define `protected static \$meta;`.");
        }
    }

    /**
     * Checks if the api class has all properties set
     *  @var string $class
     **/
    protected function hasPropertiesSet($class)
    {
        if (!$this->isRoutesDefined($class) || !$this->isVersionDefined($class)
            || !$this->isMetaDefined($class) || !$this->isRelationshipsDefined($class)
            || !$this->isModelsDefined($class)) {
            return false;
        }
        return true;
    }

    /**
     * Throw an properties undefined exception if the all properties are not defined
     *  @var string $class
     **/
    protected function hasPropertiesSetOrFail($class)
    {
        //clarify if this is true
        if (!$this->isRoutesDefined($class) || !$this->isVersionDefined($class)
            || !$this->isMetaDefined($class) || !$this->isRelationshipsDefined($class)
            || !$this->isModelsDefined($class)) {
            throw new ApiPropertiesUndefinedException("{$class} must define all these properties:  `protected static \$routes,\$models,\$relationships,\$meta;`");
        }
    }
}
