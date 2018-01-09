<?php

namespace DialInno\jaal\Middleware;

use Closure;
use Illuminate\Container\Container;
use DialInno\Jaal\Response;
use DialInno\Jaal\Objects\Document;

class NegotiateHttp
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function handle($request, Closure $next)
    {
        // make sure the HTTP headers are properly set
        $this->validateHttpHeaders($request);

        // make sure the query parameters are within recommendations
        $this->validateQueryParameters($request);

        return $next($request)->header('Content-Type', 'application/vnd.api+json');
    }

    protected function validateQueryParameters($request)
    {
        $params = array_keys($request->query->all());
        $bad_params = [];
        $reserved_params = [];

        foreach ($params as $param) {
            // these are the known acceptable query parameters
            if (!in_array($param, ['include', 'fields', 'sort', 'page', 'filter'])) {
                // anything that is lowercase only is reserved
                if ((preg_match('/^[a-z]+$/', $param) === 1)) {
                    $reserved_params[] = $param;

                // anything not like the below is just invalid
                } else if (preg_match('/^[a-zA-Z0-9]([a-zA-Z0-9_-]*[a-zA-Z0-9])?$/', $param) === 0) {
                    $bad_params[] = $param;
                }
            }
        }

        // if we have bad parameters, kick the request out
        if (count($bad_params) || count($reserved_params)) {
            (new Response(new Document))
                ->invalidQueryParam($bad_params)
                ->reservedQueryParam($reserved_params)
                ->throwResponseIfErrors();
        }
    }

    protected function validateHttpHeaders($request)
    {
        //content type must be this verbatim, unless its a request
        //that is only accepting application/vnd.api+json and not sending it.
        if ($request->header('Content-Type') !== 'application/vnd.api+json'
            && $request->header('Accept') !== 'application/vnd.api+json') {

            response(null, 415)->throwResponse();
        }

        // get the array of accept headers
        $accept_headers = explode(',', $request->header('Accept'));
        array_walk($accept_headers, function (&$item) {
            $item = trim($item);
        });

        // they must accept our content type
        if (!in_array('application/vnd.api+json', $accept_headers) && !in_array('*/*', $accept_headers)) {
            response(null, 406)->throwResponse();
        }
    }
}
