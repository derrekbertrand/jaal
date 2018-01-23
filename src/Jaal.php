<?php

namespace DialInno\Jaal;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Route;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

use DialInno\Jaal\Objects\Document;
use DialInno\Jaal\Objects\JsonApi;
use DialInno\Jaal\Objects\Meta;
use DialInno\Jaal\Objects\Links;
use DialInno\Jaal\Objects\Link;
use DialInno\Jaal\Contracts\Jaal as JaalContract;

abstract class Jaal implements JaalContract
{
    use Concerns\DefinesRoutes,
        Concerns\DefersToBuilder;

    protected $request = null;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function index(string $schema, bool $save_count = false)
    {
        $result = null;

        $this->withSchema($schema)
            ->inferSorting()
            ->inferIncluded()
            ->inferPagination($save_count);

        try {
            DB::transaction(function () use (&$result) {
                $result = $this->get();
            });
        } catch (Exception $e) {
            throw $e; // almost always a 500
        }

        return $schema::dehydrate($result, $this);
    }

    public function show(string $schema)
    {
        $result = null;

        $this->withSchema($schema)
            ->inferId()
            ->inferIncluded();

        try {
            DB::transaction(function () use (&$result) {
                $result = $this->first();
            });
        } catch (Exception $e) {
            throw $e; // almost always a 500
        }

        if ($result === null) {
            return response(null, 404);
        }

        return $schema::dehydrate($result, $this);
    }

    public function destroy(string $schema)
    {
        $result = null;

        $this->withSchema($schema)
            ->inferId();

        try {
            DB::transaction(function () use (&$result) {
                $result = $this->first();

                if ($result !== null) {
                    $result = $result->delete();
                }
            });
        } catch (Exception $e) {
            throw $e; // almost always a 500
        }

        if ($result === null) {
            return response(null, 404);
        } if ($result === false) {
            //todo: we failed to delete for some reason; figure out why and what response is appropriate
        }

        return response(null, 204);
    }

    public function globalJsonApiObject(): JsonApi
    {
        return new JsonApi(['version' => '1.0']);
    }

    public function globalMetaObject(): Meta
    {
        $meta = new Meta;

        // if we paginated and kept count, add pagination to the global Meta
        if (count($this->pagination_settings) && isset($this->pagination_settings['count'])) {
            $meta->put('record_total', $this->pagination_settings['count']);
        }

        return $meta;
    }

    public function globalBaseRoute(): string
    {
        $path = 'api.';

        if (isset(static::$route_group_settings['as'])) {
            $path .= static::$route_group_settings['as'];
        }

        return $path;
    }

    public function globalLinksObject(): Links
    {
        $links = new Links;

        $links->put('self', url()->full());

        if (count($this->pagination_settings) && isset($this->pagination_settings['number'])) {
            $q = $this->request->query->all();
            $q['page']['size'] = $this->pagination_settings['size'];
            $u = url()->current();

            $temp_q = $q;
            unset($temp_q['page']['number']);
            $links->put('first', $this->buildQuery($u, $temp_q));

            if ($this->pagination_settings['number'] > 1) {
                $temp_q = $q;
                $temp_q['page']['number'] = $this->pagination_settings['number']-1;
                $links->put('prev', $this->buildQuery($u, $temp_q));
            }

            $temp_q = $q;
            $temp_q['page']['number'] = $this->pagination_settings['number']+1;
            $links->put('next', $this->buildQuery($u, $temp_q));

            if (isset($this->pagination_settings['count'])) {
                $temp_q = $q;
                $temp_q['page']['number'] = ceil($this->pagination_settings['count']/$q['page']['size']);
                $links->put('last', $this->buildQuery($u, $temp_q));
            }
        }

        return $links;
    }

    protected function buildQuery(string $base_url, $query_data)
    {
        return $base_url.'?'.http_build_query($query_data, '', '&', PHP_QUERY_RFC3986);
    }
}
