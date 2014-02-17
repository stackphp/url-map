<?php

namespace functional;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Stack\UrlMap;
use Stack\CallableHttpKernel;

/**
 * @author Christoph Hochstrasser <christoph.hochstrasser@gmail.com>
 */
class UrlMapTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $app = new CallableHttpKernel(function (Request $request) {
            return new Response("Fallback!");
        });

        $urlMap = new UrlMap($app);
        $urlMap->setMap(array(
            '/foo' => new CallableHttpKernel(function (Request $request) {
                return new Response('foo');
            }),
        ));

        $request = Request::create('/foo');
        $response = $urlMap->handle($request);

        $this->assertEquals('foo', $response->getContent());
    }

    public function testOverridesPathInfo()
    {
        $app = new CallableHttpKernel(function (Request $request) {
            return new Response("Fallback!");
        });

        // $this do not reference the wrapping object in 5.3
        $self = $this;

        $urlMap = new UrlMap($app);
        $urlMap->setMap(array(
            '/foo' => new CallableHttpKernel(function (Request $request) use ($self) {
                $self->assertEquals('/', $request->getPathinfo());
                $self->assertEquals('/foo', $request->attributes->get(UrlMap::ATTR_PREFIX));
                $self->assertEquals('/foo', $request->getBaseUrl());

                return new Response("Hello World");
            }),
        ));

        $response = $urlMap->handle(Request::create('/foo?bar=baz'));
        $this->assertEquals('Hello World', $response->getContent());
    }
}

