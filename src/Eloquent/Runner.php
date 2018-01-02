<?php

namespace DialInno\Jaal\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Support\Collection;

class Runner
{
    /**
     * The FQCL of the primary model.
     *
     * @var Model
     **/
    protected $base_model = null;

    protected $primary_data = null;

    protected $included_data = null;

    /**
     * The Eloquent Builder that ties 
     *
     * @var Builder
     **/
    protected $baseQuery = null;

    /**
     * The models sparse fields.
     *
     * @var array
     **/
    protected $sparse = [];


    /**
     * The models sort fields.
     *
     * @var array
     **/
    protected $sort = [];

    /**
     * The number of items per page.
     *
     * @var int
     **/
    protected $page_take = 0;

    /**
     * The number of items skipped.
     *
     * @var int
     **/
    protected $page_skip = 0;

    public function setBaseModelById(string $model, ?string $id)
    {
        if ($id === null) {
            $this->setBaseModel(new $model);
        } else {
            $this->setBaseModel($model::findOrFail($id));
        }

        return $this;
    }

    public function setBaseModel(Model $model) {
        $this->base_model = $model;

        return $this;
    }

    public function getBaseModel() {
        return $this->base_model;
    }

    /**
     * Run the query to destroy the model in question.
     *
     * @return JsonApi
     */
    public function destroy()
    {
        $result = null;

        $this->assertModelExists($this->base_model);

        $result = $this->base_model->delete();

        if($result !== true)
            throw new FailedQueryException;

        return $this;
    }

    /**
     * Run the query and index the model in question.
     *
     * @return JsonApi
     */
    public function index()
    {
        // we want a bare object
        $this->assertModelDoesNotExist($this->base_model);

        $q = $this->base_model->query();

        //handle filters and search?

        $q = $this->applySort($q);

        $q = $this->applyPaginate($q);

        $this->primary_data = $q->get($this->getSparse($this->base_model));

         return $this;
    }

    /**
     * store model.
     *
     * @param  array attributes
     *
     * @return JsonApi
     */
    public function store()
    {
        $result = null;

        $this->assertModelDoesNotExist($this->base_model);

        $result = $this->base_model->save();

        return $this;
    }

    public function show()
    {
        $this->assertModelExists($this->base_model);

        $this->primary_data = $this->base_model;

        return $this;
    }

    /**
     * Update.
     *
     * @param  array attributes
     *
     * @return JsonApi
     */
    public function update()
    {
        $result = null;

        $this->assertModelExists($this->base_model);

        // note that the only difference between save and update is update checks if it exists, which we've done,
        // and it calls fill with the optional attributes, which we've supposedly done
        $result = $this->base_model->save();

        return $this;
    }

    protected function assertModelExists(?Model $model) {
        if($model === null) {
            throw new NullModelException;
        }

        if(!$model->exists) {
            throw (new BadModelException)->setModel($model);
        }
    }

    protected function assertModelDoesNotExist(?Model $model) {
        try {
            $this->assertModelExists($model);
        } catch (BadModelException $e) {
            return; // this is what is expected
        }

        throw (new BadModelException)->setModel($model);
    }

    /**
     * Set sparse fields, or empty them on a per Model basis.
     *
     * @param Model $model
     * @param array $types
     *
     * @return $this
     */
    public function setSparse(Model $model, array $fields = [])
    {
        if (count($fields)) {
            $this->sparse[get_class($model)] = array_unique(array_merge([$model->getKeyName()], $fields));
        } elseif (isset($this->sparse[get_class($model)])) {
            unset($this->sparse[get_class($model)]);
        }

        return $this;
    }

    /**
     * Get sparse fields on a per Model basis.
     *
     * @param Model $model
     *
     * @return array
     */
    public function getSparse(Model $model)
    {
        if (isset($this->sparse[get_class($model)])) {
            return $this->sparse[get_class($model)];
        } else {
            return ['*'];
        }
    }

    /**
     * Set pagination.
     *
     * @param int $take
     * @param int $skip
     *
     * @return $this
     */
    public function setPaginate(int $take, int $skip = 0)
    {
        $this->page_take = intval(max($take, 10));
        $this->page_skip = intval(max($skip, 0));

        return $this;
    }


    /**
     * Apply pagination.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    protected function applyPaginate(Builder $query)
    {
        if($this->page_take) {
            $query = $query->take($this->page_take);
        }

        if($this->page_skip) {
            $query = $query->skip($this->page_skip);
        }

        return $query;
    }

    public function setSort(array $fields = [])
    {
        //todo: apply whitelist checks

        // A decent default, as opposed to whitelists is blacklisting all hidden fields
        $hidden = $this->base_model->getHidden();
        $this->sort = array_filter($fields, function ($field) use ($hidden) {
            if (mb_substr($field, 0, 1, 'utf-8') === '-') {
                $field = mb_substr($field, 1, null, 'utf-8');
            }

            return !in_array($field, $hidden);
        });

        return $this;
    }

    /**
     * Sort the query by fields.
     *
     * @param array $fields
     *
     * @return Illuminate\Database\Query\Builder $query
     */
    protected function applySort(Builder $query)
    {
        foreach ($this->sort as $field) {
            //ascending or descending
            if (mb_substr($field, 0, 1, 'utf-8') !== '-') {
                $query = $query->orderBy($field, 'asc');
            } else {
                $query = $query->orderBy(mb_substr($field, 1, null, 'utf-8'), 'desc');
            }
        }

        return $query;
    }

    public function getPrimaryData()
    {
        return $this->primary_data;
    }

    public function model()
    {
        return $this->base_model;
    }
}
