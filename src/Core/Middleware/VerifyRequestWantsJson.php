<?php

namespace DialInno\Jaal\Core\Middleware;

use Closure;
use DialInno\Jaal\Publish\ApiV1;
use DialInno\Jaal\Core\Objects\DocObject;

class VerifyRequestWantsJson
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

        $controller = $request->route()->getController();

        //just stub out with publish copy of jsonApi
        $api = new ApiV1();

        $doc = $api->getDoc();

        $uri = $request->route()->uri();

        if ($doc->requestWantsJson()) {

            return $next($request);
        } else {
       
            if (property_exists($controller, 'exclude_routes')) { 

                $inBlackList = in_array($uri, $controller->exclude_routes)  ? true : false;
                //returns index/int or boolean -_-
                $found = array_search($uri, $controller->exclude_routes);

                //todo clean searching up && test throughly 
                $found = array_filter($controller->exclude_routes, function( $val, $key) use ($uri){
        
                    return str_contains($uri, trim($val,'/')) || str_is(trim($val,'/'), $uri);

                }, ARRAY_FILTER_USE_BOTH);

                if (!$inBlackList && empty($found)) { 

                     //todo...fix detail message slash in application/vnd..causes funky string in output
                    return $this->returnInvalidAcceptHeaderResponse($doc);
                }

                return $next($request);
               
            }

            else{
                 return $this->returnInvalidAcceptHeaderResponse($doc);
            }

        }

        return  $this->returnInvalidAcceptHeaderResponse($doc);
    }
    /**
     * Return an invalid accept header response.
     *
     * @return DialInno\Jaal\Core\Objects\DocObject $doc
     * 
     **/
    protected function returnInvalidAcceptHeaderResponse(DocObject $doc)
    {
        $doc->addError([
            'status' => '400',
            'detail' => "The request does not accept the type application/vnd.api+json, unable to process request.",
        ]);

        return $doc->getResponse();
    }
}
