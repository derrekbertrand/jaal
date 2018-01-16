<?php

namespace DialInno\Jaal;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use DialInno\Jaal\Schema;
use Route;

class QueryHelper
{
    protected $builder;
    protected $request;

    public function __construct(Builder $builder, ?Request $request = null)
    {
        $this->builder = $builder;
        $this->request = $request ?? request();
    }

    public static function fromModel(string $model_name, ?Request $request = null)
    {
        $uri_params = Route::getCurrentRoute()->parameters();
    }

    public function paginate(integer $min_limit, integer $max_limit, integer $default_limit)
    {
        // page is now a collection
        $page = collect($this->request->query->get('page', []));

        // set the defaults
        $limit = $default_limit;
        $offset = 0;

        if ($page->has('limit') || $page->has('offset')) {
            // we have either limit or offset, and they take precidence
            if (is_numeric($page->get('limit'))) {
                $limit = intval($page->get('limit'));
                $limit = max($limit, $min_limit);
                $limit = min($limit, $max_limit);
            }

            if (is_numeric($page->get('offset'))) {
                $offset = intval($page->get('offset'));
                $offset = max($offset, 0);
            }
        } else if ($page->has('size') || $page->has('number')) {
            // we have either size or number
            if (is_numeric($page->get('size'))) {
                $limit = intval($page->get('size'));
                $limit = max($limit, $min_limit);
                $limit = min($limit, $max_limit);
            }

            if (is_numeric($page->get('number'))) {
                $offset = intval($page->get('number'));
                $offset = max($offset, 0);
                $offset = $offset * $limit;
            }
        }

        $this->builder = $this->builder->skip($offset)->take($limit);

        return $this;
    }

    public function sort(array $whitelist){
        // no fields is unsafe, disallow sorting
        if (!count($whitelist)) {
            return $this;
        }

        $sort_str = $this->request->query->get('sort', '');

        // if we don't have anything, don't do anything
        if (!strlen($sort_str)) {
            return $this;
        }

        $query = $this->builder;

        foreach (explode(',', $sort_str) as $sort) {
            // set the order and properly set sort
            if (mb_substr($sort, 0, 1, 'utf-8') !== '-') {
                $ord = 'asc';
            } else {
                $ord = 'desc';
                $sort = mb_substr($sort, 1, null, 'utf-8');
            }

            if (in_array($sort, $whitelist)) {
                $query = $query->orderBy($sort, $ord);
            } else {
                // one of these is not whitelisted, so ignore the whole shebang
                return $this;
            }
        }

        // we completed the operation, so update the builder
        $this->builder = $query;

        return $this;
    }

    public function sparse(string $type, array $whitelist, array $mandatory = [])
    {
        $sparse_types = $this->request->query->get('fields', []);

        if (is_array($sparse_types) && array_key_exists($type, $sparse_types)) {
            $fields = explode(',', $sparse_types[$type]);
            $fields = array_merge(array_intersect($whitelist, $fields), $mandatory);
            $this->builder = $this->builder->select($fields);
        }

        return $this;
    }

    public function getBuilder()
    {
        return $this->builder;
    }

    public function setBuilder(Builder $builder)
    {
        $this->builder = $builder;

        return $this;
    }


}
