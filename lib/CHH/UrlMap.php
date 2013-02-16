<?php

namespace CHH;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;

class UrlMapRequest extends Request
{
    function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }
}

/**
 * URL Map Middleware, which maps kernels to paths
 *
 * Maps kernels to path prefixes and is insertable into a stack.
 *
 * @author Christoph Hochstrasser <christoph.hochstrasser@gmail.com>
 */
class UrlMap implements HttpKernelInterface
{
    protected $map = array();
    protected $app;

    function __construct(HttpKernelInterface $app, array $map = array())
    {
        $this->app = $app;

        if ($map) {
            $this->setMap($map);
        }
    }

    function setMap(array $map)
    {
        # Collect an array of all key lengths
        $lengths = array_map('strlen', array_keys($map));

        # Sort paths by their length descending, so the most specific
        # paths go first. `array_multisort` sorts the lengths descending and
        # uses the order on the $map
        array_multisort($lengths, SORT_DESC, $map);

        $this->map = $map;
    }

    function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        foreach ($this->map as $path => $app) {
            $path = str_replace('#', '\\#', $path);

            if (preg_match("#{$path}#", rawurldecode($request->getPathInfo()), $matches)) {
                $newRequest = UrlMapRequest::create(
                    $request->getRequestUri(),
                    $request->getMethod(),
                    $request->query->all(),
                    $request->cookies->all(),
                    $request->files->all(),
                    $request->server->all(),
                    $request->getContent()
                );

                $newRequest->setBaseUrl($matches[0]);
                $newRequest->attributes->add($request->attributes->all());

                $newRequest->attributes->set('spark.url_map.path', rawurldecode($matches[0]));
                $newRequest->attributes->set('spark.url_map.original_pathinfo', $request->getPathInfo());
                $newRequest->attributes->set('spark.url_map.original_request_uri', $request->getRequestUri());

                return $app->handle($newRequest, $type, $catch);
            }
        }

        return $this->app->handle($request, $type, $catch);
    }
}

