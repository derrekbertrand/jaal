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

    public function globalJsonApiObject(): JsonApi
    {
        return new JsonApi(['version' => '1.0']);
    }

    public function globalMetaObject(?int $count = null): Meta
    {
        $meta = new Meta;

        // if we paginated and kept count, add pagination to the global Meta
        if ($count !== null && count($this->pagination_settings)) {
            $meta->put('record_total', $count);
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

    public function globalLinksObject(?int $count = null): Links
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

            // count is truthy if not 0 or null
            if ($count) {
                $temp_q = $q;
                $temp_q['page']['number'] = ceil($count/$q['page']['size']);
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
