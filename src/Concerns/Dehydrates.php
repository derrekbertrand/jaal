<?php

namespace DialInno\Jaal\Concerns;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use DialInno\Jaal\Objects\Attributes;
use DialInno\Jaal\Objects\Document;
use DialInno\Jaal\Objects\Link;
use DialInno\Jaal\Objects\Links;
use DialInno\Jaal\Objects\Meta;
use DialInno\Jaal\Objects\Relationship;
use DialInno\Jaal\Objects\Relationships;
use DialInno\Jaal\Objects\Resource;
use DialInno\Jaal\Objects\ResourceIdentifier;
use DialInno\Jaal\Contracts\Response;
use DialInno\Jaal\Jaal;

trait Dehydrates
{
    /**
     * The data to dehydrate; typically returned from Eloquent queries.
     *
     * @var mixed
     */
    protected $data;

    /**
     * The Jaal API instance. If not set, links and meta data won't be added.
     *
     * @var null|Jaal
     */
    protected $jaal;

    /**
     * The Document. If not set, included will not be filled out.
     *
     * @var null|Document
     */
    protected $document;

    /**
     * Dehydrate a Document using the current Schema.
     *
     * The data parameter could be anything, and you're free to override these
     * methods to support a different data source. However, it currently
     * supports typical output of Laravel's database wrapper. You can safely
     * pass Models, Model Collections, or null.
     *
     * If you pass a Jaal Api instance it will also add links to the Document.
     *
     * @param mixed $data
     * @param Jaal $jaal
     * @return Document
     */
    public static function dehydrate($data, Jaal $jaal = null): Document
    {
        $document = new Document;

        if ($data instanceof Collection) {
            // the primary data is a Collection of Models
            $document->put('data', $data->map(function ($data_item) use ($jaal, $document) {
                return (new static)->dehydrateResource($data_item, $document, $jaal);
            })->all());
        } else if($data instanceof Model) {
            // the primary data is a model
            $document->put('data', 
                (new static)->dehydrateResource($data, $document, $jaal)
            );
        } else {
            $document->put('data', null);
        }

        // we ned a Jaal instance to have global data
        if ($jaal instanceof Jaal) {
            $document->put('jsonapi', $jaal->globalJsonApiObject());
            $document->put('meta', $jaal->globalMetaObject());
            $document->put('links', $jaal->globalLinksObject());
        }

        // ask the document to finalize any included Resources
        $document->finalizeIncluded();

        return $document;
    }

    /**
     * Dehydrate a Resource object.
     *
     * @param mixed $data
     * @return Resource
     **/
    protected function dehydrateResource($data, ?Document $document = null, ?Jaal $jaal = null): Resource
    {
        $this->data = $data;
        $this->document = $document;
        $this->jaal = $jaal;

        $resource = new Resource([
            'id' => $this->dehydrateId(),
            'type' => static::$resource_type,
        ]);

        $resource->put('attributes', $this->dehydrateAttributes());
        $resource->put('relationships', $this->dehydrateRelationships());
        $resource->put('meta', $this->dehydrateMeta());

        // add links if we have any
        $resource->put('links', $this->dehydrateLinks());

        return $resource;
    }

    /**
     * Dehydrate the attributes.
     *
     * As a sensible default, we ask the model to give us an array and then call
     * unset on the primary key. This will respect hidden, and call listed
     * mutators, so this is good enough for simple use cases.
     *
     * @return Attributes
     */
    protected function dehydrateAttributes(): Attributes
    {
        $attr = $this->data->attributesToArray();
        unset($attr[$this->data->getKeyName()]);

        return new Attributes($attr);
    }

    /**
     * Dehydrate the related data.
     *
     * @return Relationships
     */
    protected function dehydrateRelationships(): Relationships
    {
        $schemas = static::relationshipSchemas();
        $relations = $this->data->getRelations();
        $relationships = new Relationships;

        foreach ($schemas as $name => $schema) {
            $relationships->put($name, $this->dehydrateRelationship(
                isset($relations[$name]) ? $relations[$name] : false,
                $name,
                $schema
            ));
        }

        return $relationships;
    }

    /**
     * Dehydrate one relationship.
     *
     * @param mixed $data
     * @param string $name
     * @param string $schema
     * @return Relationship
     */
    protected function dehydrateRelationship($data, string $name, string $schema): Relationship
    {
        $relationship = new Relationship;

        // todo: allow each relationship to impart meta objects

        $relationship->put('links', $this->dehydrateRelationshipLinks($name));

        if ($data instanceof Collection) {
            // the related data is a collection of models
            // add an array of ResourceIdentifiers as data
            $relationship->put('data',
                $data->map(function ($data_item) use ($schema) {
                    $resource = (new $schema)->dehydrateResource($data_item, $this->document, $this->jaal);

                    if ($this->document instanceof Document) {
                        $this->document->includeResource($resource);
                    }

                    return new ResourceIdentifier($resource->only(['id', 'type']));
                })->all()
            );
        } else if($data instanceof Model) {
            // the data is a singular model
            // add its ResourceIdentifier as data
            $resource = (new $schema)->dehydrateResource($data, $this->document, $this->jaal);

            if ($this->document instanceof Document) {
                $this->document->includeResource($resource);
            }

            $relationship->put('data',
                new ResourceIdentifier($resource->only(['id', 'type']))
            );
        }

        return $relationship;
    }

    /**
     * Dehydrate links for the resource.
     *
     * @return Links
     */
    protected function dehydrateLinks(): Links
    {
        $links = new Links;

        if ($this->jaal !== null) {
            $base_route = $this->jaal->globalBaseRoute();

            $links->put('self', route($base_route.static::$resource_type.'.show', [$this->dehydrateId()]));
        }

        return $links;
    }

    /**
     * Dehydrate links for a given relationship of this resource.
     *
     * @param string $name
     * @return Links
     */
    protected function dehydrateRelationshipLinks(string $name): Links
    {
        $links = new Links;

        if ($this->jaal !== null) {
            $base_route = $this->jaal->globalBaseRoute();

            $links->put('self',route(
                $base_route.static::$resource_type.'.relationships.'.$name.'.show',
                [$this->dehydrateId()]
            ));
        }

        return $links;
    }

    /**
     * Dehydrate meta for this data source.
     *
     * @return Meta
     */
    protected function dehydrateMeta(): Meta
    {
        return new Meta;
    }

    /**
     * Get the "id" value from a data source.
     *
     * @return string
     */
    protected function dehydrateId(): string
    {
        return $this->data->getKey();
    }
}
