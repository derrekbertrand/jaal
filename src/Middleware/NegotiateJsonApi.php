<?php

namespace DialInno\Jaal\Middleware;

use Closure;
use DialInno\Jaal\Objects\ErrorObject;
use DialInno\Jaal\Api\DummyApi;

class NegotiateJsonApi
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
        // certain routes might be exempted from these checks
        if ($this->isWhitelisted($request)) {
            return $next($request);
        }

        // make sure the query parameters are within recommendations
        $paramResponse = $this->validateParameters();
        if ($paramResponse !== null) {
            return $paramResponse;
        }

        // if we specified a content type it must be this verbatim
        if ($request->header('Content-Type') !== 'application/vnd.api+json') {
            return response(null, 415);
        }

        // get the array of accept headers
        $accept_headers = explode(',', $request->header('Accept'));
        array_walk($accept_headers, function (&$item) {
            $item = trim($item);
        });

        // they must accept our content type
        if (!in_array('application/vnd.api+json', $accept_headers) && !in_array('*/*', $accept_headers)) {
            return response(null, 406);
        }

        return $next($request)->header('Content-Type', 'application/vnd.api+json');
    }

    /**
     * Determine if the current route is exempt from this middleware.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return bool
     */
    protected function isWhitelisted($request)
    {
        $route = $request->route();
        $controller = $route->getController();

        // if we have this property we may need to take action
        if (property_exists($controller, 'negotiates_json_api')) {
            if ($controller->negotiates_json_api === false) {
                return true;
            } elseif (is_array($controller->negotiates_json_api)) {
                $method = explode('@', $route->getAction()['controller'])[1];

                // if the method is listed, is is checked like it should be
                if (!in_array($method, $controller->negotiates_json_api)) {
                    return true;
                }
            } else {
                throw new \Exception('The controller should define negotiates_json_api as `public` and as either `false` or an `array`.');
            }
        }

        return false;
    }

    /**
     * Validate the URI query parameters; return null or an error Response.
     *
     * @return mixed
     */
    protected function validateParameters()
    {
        $params = array_keys($_GET);
        $bad_params = [];

        foreach ($params as $param) {
            // if it is reserved but not standard throw a fit
            if ((preg_match('/^[a-z]+$/', $param) === 1) && !in_array($param, ['include', 'fields', 'sort', 'page', 'filter', '_method'])) {
                $bad_params[] = $param;
                continue;
            }

            // if it does not match recommended specs throw a fit
            if (preg_match('/^[a-zA-Z0-9]([a-zA-Z0-9_-]*[a-zA-Z0-9])?$/', $param) === 0) {
                $bad_params[] = $param;
            }
        }

        // empty is good; nothing is wrong
        if (!count($bad_params)) {
            return null;
        }

        // we have bad parameters
        $api = new DummyApi();
        $doc = $api->getDoc();

        //add them to the object
        foreach ($bad_params as $bad_param) {
            $doc->addError(new ErrorObject($doc, [
                'title' => 'Invalid Parameter',
                'detail' => 'A specified URI parameter is not valid.',
                'status' => '400',
                'source' => ['parameter' => $bad_param],
            ]));
        }

        return $doc->getResponse();
    }
}
