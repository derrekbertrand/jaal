<?php

namespace DialInno\Jaal;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use DialInno\Jaal\Schema;
use Route;

use InvalidArgumentException;
use Illuminate\Database\QueryException;

class QueryHelper
{
    protected $builder;
    protected $request;
    protected $field_whitelist = [];

    public function __construct(Builder $builder, ?Request $request = null)
    {
        $this->builder = $builder;
        $this->request = $request ?? request();
    }

    public static function fromModel(string $model_name, ?Request $request = null)
    {
        $params = Route::getCurrentRoute()->parameters();
        $model_id = array_shift($params);

        $model = new $model_name;

        if ($model_id === null) {
            $builder = $model->query();
        } else {
            $builder = $model->where($model->getKeyName(), $model_id);
        }

        return new static($builder, $request);
    }

    public function setWhitelist(array $whitelist)
    {
        $this->field_whitelist = $whitelist;

        return $this;
    }

    public function paginate(int $min_limit, int $default_limit, int $max_limit)
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
                $offset = intval($page->get('number'))-1;
                $offset = max($offset, 0);
                $offset = $offset * $limit;
            }
        }

        $this->builder->skip($offset)->take($limit);

        return $this;
    }

    public function sort()
    {
        // no fields is unsafe, disallow sorting
        if (!count($this->field_whitelist)) {
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

            if (in_array($sort, $this->field_whitelist)) {
                $this->builder->orderBy($sort, $ord);
            }
        }

        return $this;
    }

    public function sparse(string $type, array $mandatory)
    {
        $sparse_types = $this->request->query->get('fields', []);

        if (is_array($sparse_types) && array_key_exists($type, $sparse_types)) {
            $fields = explode(',', $sparse_types[$type]);
            $fields = array_merge(array_intersect($fields, $this->field_whitelist), $mandatory);
            $this->builder->select($fields);
        }

        return $this;
    }

    public function index()
    {
        $result = null;

        try {
            DB::transaction(function () use (&$result) {
                $result = $this->builder->get();
            });
        } catch (Exception $e) {
            dd($e); // almost always a 500
        }

        return $result;
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
