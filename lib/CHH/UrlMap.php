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
    const ATTR_PREFIX = "stack.url_map.prefix";

    protected $map = array();
    protected $app;

    public function __construct(HttpKernelInterface $app, array $map = array())
    {
        $this->app = $app;

        if ($map) {
            $this->setMap($map);
        }
    }

    public function setMap(array $map)
    {
        # Collect an array of all key lengths
        $lengths = array_map('strlen', array_keys($map));

        # Sort paths by their length descending, so the most specific
        # paths go first. `array_multisort` sorts the lengths descending and
        # uses the order on the $map
        array_multisort($lengths, SORT_DESC, $map);

        $this->map = $map;
    }

    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        foreach ($this->map as $path => $app) {
            if (strpos(rawurldecode($request->getPathInfo()), $path) === 0) {
                $server = $request->server->all();
                $server['SCRIPT_FILENAME'] = $server['SCRIPT_NAME'] = $server['PHP_SELF'] = $path;

                $attributes = $request->attributes->all();
                $attributes[static::ATTR_PREFIX] = $path;

                return $app->handle($request->duplicate(null, null, $attributes, null, null, $server), $type, $catch);
            }
        }

        return $this->app->handle($request, $type, $catch);
    }
}
