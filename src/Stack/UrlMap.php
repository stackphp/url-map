<?php

namespace Stack;

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

    /**
     * @var HttpKernelInterface
     */
    protected $app;

    public function __construct(HttpKernelInterface $app, array $map = array())
    {
        $this->app = $app;

        if ($map) {
            $this->setMap($map);
        }
    }

    /**
     * Sets a map of prefixes to objects implementing HttpKernelInterface
     *
     * @param array $map
     */
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
        $pathInfo = rawurldecode($request->getPathInfo());
        foreach ($this->map as $path => $app) {
            if (0 === strpos($pathInfo, $path)) {
                $server = $request->server->all();
                $server['SCRIPT_FILENAME'] = $server['SCRIPT_NAME'] = $server['PHP_SELF'] = $request->getBaseUrl().$path;

                $attributes = $request->attributes->all();
                $attributes[static::ATTR_PREFIX] = $request->getBaseUrl().$path;

                $newRequest = $request->duplicate(null, null, $attributes, null, null, $server);

                return $app->handle($newRequest, $type, $catch);
            }
        }

        return $this->app->handle($request, $type, $catch);
    }
}
