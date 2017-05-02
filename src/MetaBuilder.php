<?php

namespace DialInno\Jaal;

use Illuminate\Support\Facades\Route;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use DialInno\Jaal\Objects\DocObject;
use DialInno\Jaal\Objects\ErrorObject;
use DialInno\Jaal\Objects\Errors\NotFoundErrorObject;

class MetaBuilder
{
    protected $config;
    protected $json_api;
    protected $models = []; //model's type identifiers (1 - n)
    protected $model_ids = []; //the ids used for lookup ()
    protected $nicknames = []; //the relation names (count($models)-1)

    /**
     * Prepare a JsonApi object based on the model types passed in.
     *
     * Note that these are referring to the models used as a base. If you are
     * accessing relationships, do not add that type here. It is specified in
     * a later call.
     *
     * @return JsonApi
     */
    public function __construct(JsonApi $json_api)
    {
        //shorthand for the config
        $this->json_api = $json_api;
        $this->config = $config;
    }

    private function __clone() {}
    private function __wakeup() {}

    protected function baseQuery()
    {
        //we work from outside in
        $models = array_reverse($this->models);
        $ids = array_reverse($this->ids);
        $nicknames = array_reverse($this->nicknames);

        //keep track of the top model
        $assoc_model = array_shift($models);

        //trivial case
        $q = $this->config['models'][$assoc_model]::query();

        //if we are looking for a specific instance
        if (count($ids) > count($models)) {
            $m = $this->config['models'][$assoc_model];
            $m = new $m;

            $q->where($m->getKeyName(), array_shift($ids));
        }

        //each model's relation
        while (count($models)) {
            //get the new model
            $assoc_model = array_shift($models);
            $nickname = array_shift($nicknames);

            $q->whereHas(camel_case($nickname), function ($query) use ($assoc_model, &$models, &$ids) {
                $m = $this->config['models'][$assoc_model];
                $m = new $m;

                $query->where($m->getKeyName(), array_shift($ids));
            });
        }

        return $q;
    }

    /**
     * Run the query to destroy the model in question.
     *
     * @return JsonApi
     */
    public function destroy()
    {
        $doc = new DocObject($this->json_api, DocObject::DOC_NONE);

        //returns true if successful
        //todo: might not always be accurate
        if (!($this->query_callable)($this->config, $this->models, $this->model_ids, $this->nicknames)->delete()) {
            $doc->addError(new NotFoundErrorObject($doc));
        }

        return $doc;
    }

    /**
     * Run the query and index the model in question.
     *
     * @return JsonApi
     */
    public function index()
    {
        $doc = new DocObject($this->json_api, DocObject::DOC_MANY);
        $request = request();

        //create the base query
        $q = ($this->query_callable)($this->config, $this->models, $this->model_ids, $this->nicknames);

        //handle filters
        try {
            $q = $this->filter($request, $q);
        } catch(\Exception $e) {
            $doc->addError(['title' => 'Filter Error', 'detail' => $e->getMessage()]);
        }

        //parse the sorting
        $q = $this->sort($request, $q);
        //parse the pagination
        $q = $this->paginate($request, $q);

        //add the paginated response to the doc
        $q->get()->each(function ($item, $key) {
            $doc->addData($item);
        });

        return $doc;
    }

    public function indexEndpoints()
    {
        $endpoints = new Collection();


        return $this->addData($endpoints)->getResponse();
    }

    public function showToMany(string $nickname)
    {
        $doc = new DocObject($this->json_api, DocObject::DOC_MANY_IDENT);

        //add the paginated response to the doc
        //todo: add exception handling
        $this->paginate(request(), ($this->query_callable)($this->config, $this->models, $this->model_ids, $this->nicknames))
            ->firstOrFail()->$nickname()->select('id')->each(function ($item, $key) {
                $doc->addData($item);
            });

        return $doc;
    }

    public function showManyToMany(string $nickname)
    {
        return $this->showToMany($nickname);
    }

    public function updateManyToMany(string $nickname, array $ids = [])
    {
        $doc = new DocObject($this->json_api, DocObject::DOC_MANY_IDENT);

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

            foreach($db_response->$nickname as $relation)
                $doc->addData($relation);
        } catch (ModelNotFoundException $e) {
            $doc->addError(new ResourceNotFoundError());
            return $doc;
        } catch (QueryException $e) {
            //dd($e->getPrevious()->errorInfo);
            $doc->addError(new DatabaseError());
            return $doc;
        } catch (\Exception $e) {
            throw $e;
        }

        return $doc;
    }

    public function destroyManyToMany(string $nickname, array $ids = [])
    {
        //todo: modification functions on relations need to not dump the whole damn relationship

        $doc = new DocObject($this->json_api, DocObject::DOC_MANY_IDENT);

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

            foreach($db_response->$nickname as $relation)
                $doc->addData($relation);
        } catch (ModelNotFoundException $e) {
            $doc->addError(new ResourceNotFoundError());
            return $doc;
        } catch (QueryException $e) {
            //dd($e->getPrevious()->errorInfo);
            $doc->addError(new DatabaseError());
            return $doc;
        } catch (\Exception $e) {
            throw $e;
        }

        return $doc;
    }

    public function storeToMany(string $nickname, array $ids = [])
    {
        $doc = new DocObject($this->json_api, DocObject::DOC_MANY_IDENT);

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
            $fk = explode('.', $db_response->$nickname()->getForeignKey())[1];
            $foreign_model = $db_response->$nickname()->getModel();

            $foreign_model::whereIn($foreign_model->getKeyName(), $ids)->update([$fk => $db_response->id]);

            foreach($db_response->$nickname as $relation)
                $doc->addData($relation);
        } catch (ModelNotFoundException $e) {
            $doc->addError(new ResourceNotFoundError());
            return $doc;
        } catch (QueryException $e) {
            //dd($e->getPrevious()->errorInfo);
            $doc->addError(new DatabaseError());
            return $doc;
        } catch (\Exception $e) {
            throw $e;
        }

        return $doc;
    }

    public function storeManyToMany(string $nickname, array $ids = [])
    {
        $doc = new DocObject($this->json_api, DocObject::DOC_MANY_IDENT);

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

            foreach($db_response->$nickname as $relation)
                $doc->addData($relation);
        } catch (ModelNotFoundException $e) {
            $doc->addError(new ResourceNotFoundError());
            return $doc;
        } catch (QueryException $e) {
            //dd($e->getPrevious()->errorInfo);
            $doc->addError(new DatabaseError());
            return $doc;
        } catch (\Exception $e) {
            throw $e;
        }

        return $doc;
    }


    public function show()
    {
        $doc = new DocObject($this->json_api, DocObject::DOC_ONE);

        try {
            //add the model
            $doc->addData(($this->query_callable)($this->config, $this->models, $this->model_ids, $this->nicknames)->firstOrFail());
        } catch (ModelNotFoundException $e) {
            $doc->addError(new NotFoundErrorObject($doc));
            return $doc;
        } catch (\Exception $e) {
            throw $e;
        }

        return $doc;
    }

    public function store(array $attributes = [])
    {
        $doc = new DocObject($this->json_api, DocObject::DOC_ONE);

        try {
            //get FQCL of model
            $model = $this->config['models'][$this->models[0]];

            $attr = count($attributes) ? $attributes : request()->all()['data']['attributes'];

            //run the query
            $doc->addData($model::create($attr));
        } catch (ModelNotFoundException $e) {
            //todo: this is not strictly accurate
            $doc->addError(new ResourceNotFoundError());
            return $doc;
        } catch (QueryException $e) {
            //dd($e->getPrevious()->errorInfo);
            $doc->addError(new DatabaseError());
            return $doc;
        } catch (\Exception $e) {
            throw $e;
        }

        return $doc;
    }

    public function update(array $attributes = [])
    {
        $doc = new DocObject($this->json_api, DocObject::DOC_ONE);

        try {
            //get the query.
            $db_response = ($this->query_callable)($this->config, $this->models, $this->model_ids, $this->nicknames)->firstOrFail();

            //drag the attributes out of the request
            $attr = count($attributes) ? $attributes : request()->all()['data']['attributes'];

            //todo: check for failed update
            $db_response->update($attr);

            $doc->addData($db_response);
        } catch (ModelNotFoundException $e) {
            $doc->addError(new NotFoundErrorObject($doc));
            return $doc;
        } catch (QueryException $e) {
            //dd($e->getPrevious()->errorInfo);
            $doc->addError(new DatabaseError());
            return $doc;
        } catch (\Exception $e) {
            throw $e;
        }

        return $doc;
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

    public function setFromController(Controller $controller)
    {
        $this->setModelIds(array_values(\Route::getCurrentRoute()->parameters()));
        $this->setModels(explode('.', array_search(get_class($controller), $this->config['routes'])));

        return $this;
    }

    protected function filter(Request $request, Builder $query)
    {
        if(!strlen($request->input('filter.rql')))
            return $query;

        //todo: add query builder
        //$query = RqlBuilder::append($query, $request->input('filter.rql'))->getBuilder();

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
}
