<?php

namespace DialInno\Jaal\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use DialInno\Jaal\Schema;
use Route;

use InvalidArgumentException;
use RuntimeException;
use Illuminate\Database\QueryException;

trait DefersToBuilder
{
    protected $builder; // a query builder or relation instance; must be set for these methods to work
    protected $schema; // we use this to determine how to handle certain query actions
    protected $pagination_settings = [];

    public function inferPagination(bool $save_count = false)
    {
        // page is now a collection
        $page = collect($this->request->query->get('page', []));

        // set the defaults
        $limit = $this->schema::$default_limit;
        $offset = 0;

        if ($page->has('limit') || $page->has('offset')) {
            // we have either limit or offset, and they take precidence
            if (is_numeric($page->get('limit'))) {
                $limit = intval($page->get('limit'));
                $limit = max($limit, $this->schema::$min_limit);
                $limit = min($limit, $this->schema::$max_limit);
            }

            if (is_numeric($page->get('offset'))) {
                $offset = intval($page->get('offset'));
                $offset = max($offset, 0);
            }

            $this->pagination_settings['limit'] = $limit;
            $this->pagination_settings['offset'] = $offset;

        } else if ($page->has('size') || $page->has('number')) {
            // we have either size or number
            if (is_numeric($page->get('size'))) {
                $limit = intval($page->get('size'));
                $limit = max($limit, $this->schema::$min_limit);
                $limit = min($limit, $this->schema::$max_limit);
            }

            if (is_numeric($page->get('number'))) {
                $offset = intval($page->get('number'))-1;
                $offset = max($offset, 0);
                $offset = $offset * $limit;
            }

            $this->pagination_settings = [
                'size' => $limit, 
                'number' => intdiv($offset, $limit)+1,
            ];
        }

        if ($save_count) {
            $this->pagination_settings['count'] = $this->builder->count();
        }

        $this->builder->skip($offset)->take($limit);

        return $this;
    }

    public function inferSorting()
    {
        $whitelist = $this->schema::$sort_whitelist;

        // no fields is unsafe, disallow sorting
        if (!count($whitelist)) {
            return $this;
        }

        $sort_str = $this->request->query->get('sort', '');

        // if we don't have anything, don't do anything
        if (!strlen($sort_str)) {
            return $this;
        }

        foreach (explode(',', $sort_str) as $sort) {
            // set the order and properly set sort
            if (mb_substr($sort, 0, 1, 'utf-8') !== '-') {
                $ord = 'asc';
            } else {
                $ord = 'desc';
                $sort = mb_substr($sort, 1, null, 'utf-8');
            }

            if (in_array($sort, $whitelist)) {
                $this->builder->orderBy($sort, $ord);
            }
        }

        return $this;
    }

    public function inferIncluded()
    {
        $whitelist = array_keys($this->schema::relationshipSchemas());

        // no fields is unsafe, disallow including
        if (!count($whitelist)) {
            return $this;
        }

        $include_str = $this->request->query->get('include', '');

        foreach(explode(',', $include_str) as $include) {
            if (in_array($include, $whitelist)) {
                $this->builder->with($include);
            }
        }

        return $this;
    }

    public function inferId()
    {
        $key_name = $this->schema::$exposed_key;
        $params = Route::getCurrentRoute()->parameters();

        $this->builder->where($key_name, array_shift($params));

        return $this;
    }

    public function getBuilder()
    {
        return $this->builder;
    }

    /**
     * Set the schema.
     *
     * It accepts a Schema name.
     *
     * @param mixed $schema
     * @return $this
     */
    public function withSchema(string $schema)
    {
        $this->schema = $schema;
        $this->builder = (new $schema::$model)->query();

        return $this;
    }

    /**
     * Handle dynamic method calls into the model.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if ($this->builder === null) {
            throw new RuntimeException('Cannot defer to Eloquent; builder not set when calling method: '.$method);
        }

        $result = $this->builder->$method(...$parameters);

        // if what was returned is a Builder or Relation, then continue maintaining the chain
        if ($result instanceof Builder || $result instanceof Relation) {
            $this->builder = $result;
            return $this;
        }

        // the result was not query related, so return the value
        return $result;
    }
}
