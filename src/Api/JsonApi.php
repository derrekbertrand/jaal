<?php

namespace DialInno\Jaal\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Route;
use DialInno\Jaal\Objects\DocObject;
use Illuminate\Database\Eloquent\Builder;
use DialInno\Jaal\Objects\Errors\NotFoundErrorObject;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use DialInno\Jaal\Exceptions\UndefinedApiPropertiesException;

abstract class JsonApi
{
    /**
     * The current request models were
     * working with.
     *
     * @var array
     **/
    protected $context_models;

    /**
     * The current request models were
     * working with.
     *
     * @var array
     **/
    protected $context_models_ids;

    /**
     * The doc object to return.
     *
     * @var \DialInno\Jaal\Core\Objects\DocObject
     **/
    protected $doc;

    /**
     * The context models nicknames as we defined in the static property $models.
     *
     * @var array
     **/
    protected $nicknames = [];

    /**
     * The models sparse fields.
     *
     * @var array
     **/
    protected $sparse_fields = [];

    private $requiredProperties = ['routes', 'models', 'version'];

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
        //we keep an internal doc so we can make a response
        $this->doc = new DocObject($this);

        //verify that our required fields are present...maybe move this somewhere else?
        $class = get_called_class();
        foreach ($this->requiredProperties as $property) {
            if (!property_exists($class, $property)) {
                throw new UndefinedApiPropertiesException("$class must define `protected static \${$property};`.");
            }
        }
    }

    /**
     * Add the user's defined meta into the document.
     *
     * @var
     **/
    protected function addMetaIfDefined($meta_data)
    {
        if (property_exists($this, 'meta') && !empty($meta_data)) {
            $this->getDoc()->addMeta($meta_data);
        }

        return $this;
    }

    /**
     * Build up the query for the context model(s).
     * and find.
     *
     * @var array
     **/
    protected function baseQuery()
    {
        //we work from outside in
        $models = array_reverse($this->context_models);

        $ids = array_reverse($this->current_model_ids);

        $nicknames = array_reverse($this->nicknames);

        //keep track of the top model
        $assoc_model = array_shift($models);

        //trivial case
        $q = static::$models[$assoc_model]::query();

        //if we are looking for a specific instance
        if (count($ids) > count($models)) {
            $m = static::$models[$assoc_model];

            $m = new $m();

            $q->where($m->getKeyName(), array_shift($ids));
        }

        //do sparse fields on the main model
        //todo: sparse fields on other models?
        if (isset($this->sparse_fields[$assoc_model])) {
            call_user_func_array([$q, 'select'], $this->sparse_fields[$assoc_model]);
        }

        //each model's relation
        while (count($models)) {
            //get the new model
            $assoc_model = array_shift($models);
            $nickname = array_shift($nicknames);

            $q->whereHas(camel_case($nickname), function ($query) use ($assoc_model, &$models, &$ids) {
                $m = static::$models[$assoc_model];
                $m = new $m();

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
        $this->doc = new DocObject($this, DocObject::DOC_NONE);

        //todo: differentiate between a not found and a failed delete
        try {
            //returns true if successful
            if (!$this->baseQuery()->firstOrFail()->delete()) {
                throw new \Exception();
            }
        } catch (\Exception $e) {
            $this->doc->addError(new NotFoundErrorObject($this->doc));
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

        $this->doc = new DocObject($this, DocObject::DOC_MANY);

        // add the user's defined meta to the document response.
        $this->addMetaIfDefined(static::$meta);

        //handle sparse fields
        try {
            $this->sparse($request);
        } catch (\Exception $e) {
            $this->doc->addError(['title' => 'Sparse Fieldset Error', 'detail' => $e->getMessage()]);
        }

        //create the base query
        $q = $this->baseQuery();

        //handle filters
        try {
            $q = $this->filter($request, $q);
        } catch (\Exception $e) {
            $this->doc->addError(['title' => 'Filter Error', 'detail' => $e->getMessage()]);
        }

        //parse the sorting
        try {
            $q = $this->sort($request, $q);
        } catch (\Exception $e) {
            $this->doc->addError(['title' => 'Sorting Error', 'detail' => $e->getMessage()]);
        }

        //parse the pagination
        try {
            $q = $this->paginate($request, $q);
        } catch (\Exception $e) {
            $this->doc->addError(['title' => 'Pagination Error', 'detail' => $e->getMessage()]);
        }

        //add the paginated response to the doc
        $q->get()->each(function ($item, $key) {
            $this->doc->addData($item);
        });

        return $this;
    }

    public function showToMany(string $nickname)
    {
        $this->doc = new DocObject($this, DocObject::DOC_MANY_IDENT);

        //add the paginated response to the doc
        //todo: add exception handling
        $this->paginate(
            request(),
            $this->baseQuery()
                ->firstOrFail()
                ->$nickname()
                ->getQuery()
            )
            ->each(function ($item, $key) {
                $this->doc->addData($item);
            });

        return $this;
    }

    public function showToOne(string $nickname)
    {
        $this->doc = new DocObject($this, DocObject::DOC_ONE_IDENT);
        $res = null;

        //add the paginated response to the doc
        try {
            $res = $this->baseQuery()->firstOrFail()->$nickname;
        } catch (ModelNotFoundException $e) {
            $this->doc->addError(new NotFoundErrorObject($this->doc));
        } catch (\Exception $e) {
            dd($e);
        }

        if ($res !== null) {
            $this->doc->addData($res);
        }

        return $this;
    }

    public function updateToOne(string $nickname, $id = null)
    {
        $this->doc = new DocObject($this, DocObject::DOC_ONE_IDENT);
        $body = json_decode(request()->getContent(), true) ?: request()->all();

        //if we don't have an id, and we do have one in the body...
        if ($id === null && array_key_exists('data', $body) && array_key_exists('id', $body['data'])) {
            $id = $body['data']['id'];
        }

        $res = $this->baseQuery()->firstOrFail();
        $res->$nickname()->associate($id)->save();

        if ($id !== null) {
            $this->doc->addData($res->$nickname);
        }

        return $this;
    }

    public function showManyToMany(string $nickname)
    {
        return $this->showToMany($nickname);
    }

    public function updateManyToMany(string $nickname, array $ids = [])
    {
        $this->doc = new DocObject($this, DocObject::DOC_MANY_IDENT);

        try {
            //get the query.
            $db_response = $this->baseQuery()->firstOrFail();

            //drag the ids out of the request
            if (!count($ids)) {
                $body = json_decode(request()->getContent(), true) ?: request()->all();
                foreach ($body['data'] as $rid) {
                    $ids[] = $rid['id'];
                }
            }

            //todo: check for failed update
            //todo: check class type of incoming data: get_class($faq->products()->getRelated())
            //this passes back meta info about the query
            $db_response->$nickname()->sync($ids);

            foreach ($db_response->$nickname as $relation) {
                $this->doc->addData($relation);
            }
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
        //todo: modification functions on relations need to not dump the whole damn relationship

        $this->doc = new DocObject($this, DocObject::DOC_MANY_IDENT);

        try {
            //get the query.
            $db_response = $this->baseQuery()->firstOrFail();

            //drag the ids out of the request
            if (!count($ids)) {
                $body = json_decode(request()->getContent(), true) ?: request()->all();
                foreach ($body['data'] as $rid) {
                    $ids[] = $rid['id'];
                }
            }

            //todo: check for failed update
            $db_response->$nickname()->detach($ids);

            foreach ($db_response->$nickname as $relation) {
                $this->doc->addData($relation);
            }
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

    public function storeToMany(string $nickname, array $ids = [])
    {
        $this->doc = new DocObject($this, DocObject::DOC_MANY_IDENT);

        try {
            //get the query.
            $db_response = $this->baseQuery()->firstOrFail();

            //drag the ids out of the request
            if (!count($ids)) {
                $body = json_decode(request()->getContent(), true) ?: request()->all();
                foreach ($body['data'] as $rid) {
                    $ids[] = $rid['id'];
                }
            }

            //todo: check for failed update
            $fk = explode('.', $db_response->$nickname()->getForeignKey())[1];
            $foreign_model = $db_response->$nickname()->getModel();

            $foreign_model::whereIn($foreign_model->getKeyName(), $ids)->update([$fk => $db_response->id]);

            foreach ($db_response->$nickname as $relation) {
                $this->doc->addData($relation);
            }
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
        $this->doc = new DocObject($this, DocObject::DOC_MANY_IDENT);

        try {
            //get the query.
            $db_response = $this->baseQuery()->firstOrFail();

            //drag the ids out of the request
            if (!count($ids)) {
                $body = json_decode(request()->getContent(), true) ?: request()->all();
                foreach ($body['data'] as $rid) {
                    $ids[] = $rid['id'];
                }
            }

            //todo: check for failed update
            $db_response->$nickname()->syncWithoutDetaching($ids);

            foreach ($db_response->$nickname as $relation) {
                $this->doc->addData($relation);
            }
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

    /**
     * add the model to the document if its available.
     * or add a not found error to the document response.
     **/
    public function show()
    {
        $this->doc = new DocObject($this, DocObject::DOC_ONE);

        try {
            //add the model
            $this->doc->addData($this->baseQuery()->firstOrFail());
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
        $this->doc = new DocObject($this, DocObject::DOC_ONE);

        try {
            //get FQCL of model
            $model = static::$models[$this->context_models[0]];

            $body = json_decode(request()->getContent(), true) ?: request()->all();
            $attr = count($attributes) ? $attributes : $body['data']['attributes'];

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
        $this->doc = new DocObject($this, DocObject::DOC_ONE);

        try {
            //get the query.
            $db_response = $this->baseQuery()->firstOrFail();

            //drag the attributes out of the request
            $body = json_decode(request()->getContent(), true) ?: request()->all();
            $attr = count($attributes) ? $attributes : $body['data']['attributes'];

            //todo: check for failed update
            $db_response->update($attr);

            $this->doc->addData($db_response);
        } catch (ModelNotFoundException $e) {
            $this->doc->addError(new NotFoundErrorObject($this->doc));

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

    /**
     * Set the current request's models
     * we are working with.
     *
     * @var array
     **/
    public function setContextModels(array $api_models)
    {
        $this->context_models = $api_models;
    }

    /**
     * Set the current request's models id's
     * we are working with.
     *
     * @var array
     **/
    public function setContextModelIds(array $modelIds)
    {
        $this->current_model_ids = $modelIds;
    }

    public function setNicknames(array $nicknames)
    {
        $this->nicknames = $nicknames;

        return $this;
    }

    public function getDoc()
    {
        return $this->doc;
    }

    /**
     * Infer all of the data.
     **/
    public function inferAll()
    {
        $caller = debug_backtrace(false, 2)[1];
        $this->setContextModelIds(array_values(\Route::getCurrentRoute()->parameters()));
        //return the nicknames of the models as defined in routes statick property
        $this->setContextModels(explode('.', array_search($caller['class'], static::$routes)));
        $this->{$caller['function']}();

        return $this;
    }

    public function inferQueryParam(Controller $controller)
    {
        $this->setContextModelIds(array_values(\Route::getCurrentRoute()->parameters()));
        $this->setContextModels(explode('.', array_search(get_class($controller), static::$routes)));

        return $this;
    }

    protected function filter(Request $request, Builder $query)
    {
        if (strlen($request->input('filter.search', ''))) {
            $query = $this->search($query, $this->context_models[count($this->context_models) - 1], explode(' ', substr($request->input('filter.search'), 0, 31)));
        }

        //todo: add query builder
        //$query = RqlBuilder::append($query, $request->input('filter.rql'))->getBuilder();

        return $query;
    }

    protected function sparse(Request $request)
    {
        $types = $request->input('fields', null);

        //todo: default sparse fields per model
        if (is_null($types)) {
            return;
        }
        foreach ($types as $type => $fields_str) {
            if (strlen($fields_str)) {
                $this->sparse_fields[$type] = array_unique(array_merge(['id'], explode(',', $fields_str)));
            } else {
                $this->sparse_fields[$type] = ['id'];
            }
        }
    }

    protected function paginate(Request $request, Builder $query)
    {
        $page_offset = max(0, intval($request->input('page.offset', 0)));
        $page_limit = min(200, max(10, intval($request->input('page.limit', 15))));

        if (in_array($this->context_models[0], static::$pagination_data)) {
            //run the base query with count()
            $count = $query->count();

            //todo: add pagination links to base doc

            //add total to metadata
            $this->getDoc()->addMeta([
                'record_total' => $count,
                'record_offset' => $page_offset,
            ]);
        }

        $query = $query->take($page_limit)->skip($page_offset);

        return $query;
    }

    protected function sort(Request $request, Builder $query)
    {
        $sort_str = $request->input('sort', '');

        //todo: default sorting per model
        if (!strlen($sort_str)) {
            return $query;
        }

        //get all the things we need to sort by
        $sort_arr = explode(',', $sort_str);

        foreach ($sort_arr as $sort) {
            //todo: whitelist

            //ascending or descending
            if (mb_substr($sort, 0, 1, 'utf-8') !== '-') {
                $query = $query->orderBy($sort, 'asc');
            } else {
                $query = $query->orderBy(mb_substr($sort, 1, null, 'utf-8'), 'desc');
            }
        }

        return $query;
    }

    /**
     * Generate a set of routes based on the eloquent jsonify config.
     */
    public static function routes()
    {
        $api = new static();
        //make sure this class has all the properties it needs.

        //get the group they want
        $routes = is_array(static::$routes) && !empty(static::$routes) ? static::$routes : [];

        $relationships = is_array(static::$relationships) && !empty(static::$relationships) ? static::$relationships : [];

        //LEFT OFF Here

        //define the common routes

        foreach ($routes as $name => $controller) {


            Route::resource($name, '\\'.$controller, ['except' => ['create', 'edit']]);
        }

        //define relationship routes
        foreach ($relationships as $from => $all_relations) {
            foreach ($all_relations as $nickname => $rel_type){

                $controller = static::$routes[$from];
                $studlyNickName = studly_case($nickname);
                Route::get("$from/{{$from}}/relationships/$nickname", [
                    'as' => "$from.relationships.$nickname.show",
                    'uses' => '\\'.$controller.'@show'.$studlyNickName,
                ]);

                Route::patch("$from/{{$from}}/relationships/$nickname", [
                    'as' => "$from.relationships.$nickname.update",
                    'uses' => '\\'.$controller.'@update'.$studlyNickName,
                ]);

                //if it is a to-many type, then we need to respond to these also
                if ($rel_type != 'to-one') {
                    //post for add only
                    Route::post("$from/{{$from}}/relationships/$nickname", [
                        'as' => "$from.relationships.$nickname.store",
                        'uses' => '\\'.$controller.'@store'.$studlyNickName,
                    ]);

                    //delete for drop only
                    Route::delete("$from/{{$from}}/relationships/$nickname", [
                        'as' => "$from.relationships.$nickname.destroy",
                        'uses' => '\\'.$controller.'@destroy'.$nickname,
                    ]);
                }
            }
        }
    }

    /**
     * Search using a query string.
     *
     * @param Builder $query
     * @param string  $type
     * @param array   $search
     *
     * @return Builder
     */
    protected function search($query, $type, $search)
    {
        //by deafult, we just ignore search queries
        //this behavior can be easily overriden on a per API basis
        return $query;
    }
}