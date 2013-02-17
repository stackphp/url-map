<?php

namespace CHH;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;

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
            if (strpos(rawurldecode($request->getPathInfo()), $path) === 0) {
                $server = array(
                    'SCRIPT_NAME' => rtrim($path, '/') . '/' . $request->server->get('SCRIPT_NAME')
                );

                $subRequest = $request->duplicate(null, null, null, null, null, $server);
                return $app->handle($subRequest, $type, $catch);
            }
        }

        return $this->app->handle($request, $type, $catch);
    }
}

