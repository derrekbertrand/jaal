<?php

namespace DialInno\Jaal;

use Illuminate\Support\Facades\Route;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use DialInno\Jaal\Rql\RqlBuilder;
use DialInno\Jaal\Objects\DocObject;
use DialInno\Jaal\Objects\ErrorObject;
use DialInno\Jaal\Objects\Errors\NotFoundErrorObject;

abstract class JsonApi
{
    protected $config = null;
    protected $query_callable;
    protected $models = [];
    protected $model_ids = [];
    protected $nicknames = [];
    protected $doc;
    protected static $instance = null;

    /**
     * Prepare a JsonApi object based on the model types passed in.
     *
     * Note that these are referring to the models used as a base. If you are
     * accessing relationships, do not add that type here. It is specified in
     * a later call.
     *
     * @return JsonApi
     */
    public function __construct()
    {
        if(!isset(static::$api_version) && !strlen(static::$api_version))
            throw new \Exception('JsonApi must define `protected static $api_version;`.');

        //this is a singleton
        if(static::$instance !== null)
            return static::$instance;

        //shorthand for the config
        $this->config = config('jaal.'.static::$api_version);

        //we keep an internal doc so we can make a response
        $this->doc = new DocObject($this);

        //get the callback
        $this->query_callable = $this->defaultQueryCallable();

        //just to check, you never know about people
        if(!is_callable($this->query_callable))
            throw new \Exception('defaultQueryCallable() must return a callable function.');

    }

    private function __clone() {}
    private function __wakeup() {}

    protected function defaultQueryCallable()
    {
        return function ($config, $models, $ids, $nicknames) {
            //we work from outside in
            $models = array_reverse($models);
            $ids = array_reverse($ids);
            $nicknames = array_reverse($nicknames);

            //keep track of the top model
            $assoc_model = array_shift($models);

            //trivial case
            $q = $config['models'][$assoc_model]::query();

            //if we are looking for a specific instance
            if (count($ids) > count($models)) {
                $m = $config['models'][$assoc_model];
                $m = new $m;

                $q->where($m->getKeyName(), array_shift($ids));
            }

            //each model's relation
            while (count($models)) {
                //get the new model
                $assoc_model = array_shift($models);
                $nickname = array_shift($nicknames);

                $q->whereHas(camel_case($nickname), function ($query) use ($config, $assoc_model, &$models, &$ids) {
                    $m = $config['models'][$assoc_model];
                    $m = new $m;

                    $query->where($m->getKeyName(), array_shift($ids));
                });
            }

            return $q;
        };
    }

    /**
     * Run the query to destroy the model in question.
     *
     * @return JsonApi
     */
    public function destroy()
    {
        //returns true if successful
        if (!($this->query_callable)($this->config, $this->models, $this->model_ids, $this->nicknames)->delete()) {
            $this->doc->addError(['title' => 'Resource Not Found', 'detail' => 'The resource does not exist.']);
        }

        return $this;
    }

    /**
     * Run the query and index the model in question.
     *
     * @return JsonApi
     */
    public function index()
    {
        //get the default request
        //in the future, we might have them pass it in or something
        $request = request();

        $this->doc->setMany();

        //create the base query
        $q = ($this->query_callable)($this->config, $this->models, $this->model_ids, $this->nicknames);

        //handle filters
        try {
            $q = $this->filter($request, $q);
        } catch(\Exception $e) {
            $this->doc->addError(['title' => 'Filter Error', 'detail' => $e->getMessage()]);
        }

        //parse the sorting
        $q = $this->sort($request, $q);
        //parse the pagination
        $q = $this->paginate($request, $q);

        //add the paginated response to the doc
        $q->get()->each(function ($item, $key) {
            $this->doc->addData($item);
        });

        return $this;
    }

    public function indexEndpoints()
    {
        $endpoints = new Collection();


        return $this->getDoc()->addData($endpoints)->getResponse();
    }

    public function showToMany(string $nickname)
    {
        //add the paginated response to the doc
        //todo: add exception handling
        $this->paginate(($this->query_callable)($this->config, $this->models, $this->model_ids, $this->nicknames))
            ->firstOrFail()->$nickname->each(function ($item, $key) {
                $this->doc->addData($item);
            });

        return $this;
    }

    public function showManyToMany(string $nickname)
    {
        return $this->showToMany($nickname);
    }

    public function updateManyToMany(string $nickname, array $ids = [])
    {
        try {
            //get the query.
            $db_response = ($this->query_callable)($this->config, $this->models, $this->model_ids, $this->nicknames)->firstOrFail();

            //drag the ids out of the request
            if(!count($ids))
            {
                foreach(request()->all()['data'] as $rid)
                    $ids[] = $rid['id'];
            }

            //todo: check for failed update
            //todo: check class type of incoming data: get_class($faq->products()->getRelated())
            //this passes back meta info about the query
            $db_response->$nickname()->sync($ids);

            //todo: fix without using an array; it is an Eloquent integration
            //$this->doc->addArrayCollection(collect(request()->all()['data']), true);
        } catch (ModelNotFoundException $e) {
            $this->doc->addError(new ResourceNotFoundError());
            return $this;
        } catch (QueryException $e) {
            //dd($e->getPrevious()->errorInfo);
            $this->doc->addError(new DatabaseError());
            return $this;
        } catch (\Exception $e) {
            throw $e;
        }

        return $this;
    }

    public function destroyManyToMany(string $nickname, array $ids = [])
    {
        try {
            //get the query.
            $db_response = ($this->query_callable)($this->config, $this->models, $this->model_ids, $this->nicknames)->firstOrFail();

            //drag the ids out of the request
            if(!count($ids))
            {
                foreach(request()->all()['data'] as $rid)
                    $ids[] = $rid['id'];
            }

            //todo: check for failed update
            $db_response->$nickname()->detach($ids);

            $this->doc->addData($db_response->$nickname, true);
        } catch (ModelNotFoundException $e) {
            $this->doc->addError(new ResourceNotFoundError());
            return $this;
        } catch (QueryException $e) {
            //dd($e->getPrevious()->errorInfo);
            $this->doc->addError(new DatabaseError());
            return $this;
        } catch (\Exception $e) {
            throw $e;
        }

        return $this;
    }

    public function storeManyToMany(string $nickname, array $ids = [])
    {
        try {
            //get the query.
            $db_response = ($this->query_callable)($this->config, $this->models, $this->model_ids, $this->nicknames)->firstOrFail();

            //drag the ids out of the request
            if(!count($ids))
            {
                foreach(request()->all()['data'] as $rid)
                    $ids[] = $rid['id'];
            }

            //todo: check for failed update
            $db_response->$nickname()->syncWithoutDetaching($ids);

            $this->doc->addData($db_response->$nickname, true);
        } catch (ModelNotFoundException $e) {
            $this->doc->addError(new ResourceNotFoundError());
            return $this;
        } catch (QueryException $e) {
            //dd($e->getPrevious()->errorInfo);
            $this->doc->addError(new DatabaseError());
            return $this;
        } catch (\Exception $e) {
            throw $e;
        }

        return $this;
    }


    public function show()
    {
        try {
            //add the model
            $this->doc->addData(($this->query_callable)($this->config, $this->models, $this->model_ids, $this->nicknames)->firstOrFail());
        } catch (ModelNotFoundException $e) {
            $this->doc->addError(new NotFoundErrorObject($this->doc));
            return $this;
        } catch (\Exception $e) {
            throw $e;
        }

        return $this;
    }

    public function store(array $attributes = [])
    {
        try {
            //get FQCL of model
            $model = $this->config['models'][$this->models[0]];

            $attr = count($attributes) ? $attributes : request()->all()['data']['attributes'];

            //run the query
            $this->doc->addData($model::create($attr));
        } catch (ModelNotFoundException $e) {
            //todo: this is not strictly accurate
            $this->doc->addError(new ResourceNotFoundError());
            return $this;
        } catch (QueryException $e) {
            //dd($e->getPrevious()->errorInfo);
            $this->doc->addError(new DatabaseError());
            return $this;
        } catch (\Exception $e) {
            throw $e;
        }

        return $this;
    }

    public function update(array $attributes = [])
    {
        try {
            //get the query.
            $db_response = ($this->query_callable)($this->config, $this->models, $this->model_ids, $this->nicknames)->firstOrFail();

            //drag the attributes out of the request
            $attr = count($attributes) ? $attributes : request()->all()['data']['attributes'];

            //todo: check for failed update
            $db_response->update($attr);

            $this->doc->addData($db_response);
        } catch (ModelNotFoundException $e) {
            $this->doc->addError(new ResourceNotFoundError());
            return $this;
        } catch (QueryException $e) {
            //dd($e->getPrevious()->errorInfo);
            $this->doc->addError(new DatabaseError());
            return $this;
        } catch (\Exception $e) {
            throw $e;
        }

        return $this;
    }

    public function setQueryCallable(callable $baseQueryCallable)
    {
        $this->query_callable = $baseQueryCallable;

        return $this;
    }

    public function setModelIds(array $modelIds)
    {
        $this->model_ids = $modelIds;

        return $this;
    }

    public function setModels(array $models)
    {
        $this->models = $models;

        return $this;
    }

    public function setNicknames(array $nicknames)
    {
        $this->nicknames = $nicknames;

        return $this;
    }

    public function getResponse()
    {
        return $this->doc->getResponse();
    }

    public function getDoc()
    {
        return $this->doc;
    }

    public function inferQueryParam(Controller $controller)
    {
        $this->setModelIds(array_values(\Route::getCurrentRoute()->parameters()));
        $this->setModels(explode('.', array_search(get_class($controller), $this->config['routes'])));

        return $this;
    }

    protected function search(Request $request, Builder $query)
    {
        //the current model is the last model
        $model = $this->config['models'][$this->models[count($this->models)-1]];

        //must have a search query
        if(!strlen($request->input('filter.search')))
            return $query;

        try {
            $query = $model::jsonApiSearch($query, explode(' ', $request->input('filter.search')));
        } catch(\Exception $e)
        {
            // might fail, due to the method not existing, but don't fret
            //todo: handle some edge cases here; not everybody wants this behavior
        }

        return $query;
    }

    protected function filter(Request $request, Builder $query)
    {
        if(!strlen($request->input('filter.rql')))
            return $query;

        $query = RqlBuilder::append($query, $request->input('filter.rql'))->getBuilder();

        return $query;
    }

    /**
     *
     */
    protected function paginate(Request $request, Builder $query)
    {
        $page_offset = (int) $request->input('page.offset', 0);
        $page_limit = (int) $request->input('page.limit', 15);

        //todo: bounds check page limit

        // return $query->take($page_size)->skip(($page_number-1)*$page_size);
        return $query->take($page_limit)->skip($page_offset);
    }

    protected function sort(Request $request, Builder $query)
    {
        $sort_str = $request->input('sort', null);

        //todo: default sorting per model
        if(is_null($sort_str))
            return $query;

        //get all the things we need to sort by
        $sort_arr = explode(',', $sort_str);

        foreach($sort_arr as $sort)
        {
            //todo: whitelist

            //ascending or descending
            if(mb_substr($sort, 0, 1, 'utf-8') !== '-')
                $query = $query->orderBy($sort, 'asc');
            else
                $query = $query->orderBy(mb_substr($sort, 1, null, 'utf-8'), 'desc');
        }

        return $query;
    }

    /**
     * Generate a set of routes based on the eloquent jsonify config.
     *
     * @return null
     */
    public static function routes()
    {
        if(!isset(static::$api_version) && !strlen(static::$api_version))
            throw new \Exception('JsonApi must define `protected static $api_version;`.');

        Route::get(null, [
            'as' => 'index-endpoints',
            'uses' => '\\'.static::class.'@indexEndpoints',
        ]);

        //get the group they want
        $routes = config('jaal.'.static::$api_version.'.routes');
        $relationships = config('jaal.'.static::$api_version.'.relationships');

        //define the common routes
        foreach ($routes as $name => $controller) {
            Route::resource($name, '\\'.$controller, ['except' => ['create', 'edit']]);
        }

        //define relationship routes
        foreach ($relationships as $from => $all_relations) {
            foreach ($all_relations as $nickname => $rel_type) {
                $controller = config('jaal.'.static::$api_version.'.routes.'.$from);

                Route::get("$from/{{$from}}/relationships/$nickname", [
                    'as' => "$from.relationships.$nickname.show",
                    'uses' => '\\'.$controller.'@show'.studly_case($nickname)
                ]);

                Route::patch("$from/{{$from}}/relationships/$nickname", [
                    'as' => "$from.relationships.$nickname.update",
                    'uses' => '\\'.$controller.'@update'.studly_case($nickname)
                ]);

                //if it is a to-many type, then we need to respond to these also
                if ($rel_type != 'to-one') {
                    //post for add only
                    Route::post("$from/{{$from}}/relationships/$nickname", [
                        'as' => "$from.relationships.$nickname.store",
                        'uses' => '\\'.$controller.'@store'.studly_case($nickname)
                    ]);

                    //delete for drop only
                    Route::delete("$from/{{$from}}/relationships/$nickname", [
                        'as' => "$from.relationships.$nickname.destroy",
                        'uses' => '\\'.$controller.'@destroy'.studly_case($nickname)
                    ]);
                }
            }
        }
    }
}
