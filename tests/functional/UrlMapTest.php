<?php

namespace functional;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Stack\UrlMap;
use Stack\CallableHttpKernel;
use PHPUnit\Framework\TestCase;

/**
 * @author Christoph Hochstrasser <christoph.hochstrasser@gmail.com>
 */
class UrlMapTest extends TestCase
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
        $urlMap->terminate($request, $response);

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

        $response = $urlMap->handle($request = Request::create('/foo?bar=baz'));
        $urlMap->terminate($request, $response);

        $this->assertEquals('Hello World', $response->getContent());
    }

    public function testShouldBeStackable()
    {
        $app = new CallableHttpKernel(function (Request $request) {
            return new Response("Fallback!");
        });

        // $this do not reference the wrapping object in 5.3
        $self = $this;

        $urlMapInner = new UrlMap($app);
        $urlMapInner->setMap(array(
            '/bar' => new CallableHttpKernel(function (Request $request) use ($self) {
                $self->assertEquals('/', $request->getPathinfo());
                $self->assertEquals('/foo/bar', $request->attributes->get(UrlMap::ATTR_PREFIX));
                $self->assertEquals('/foo/bar', $request->getBaseUrl());

                return new Response("Hello World");
            }),
        ));

        $urlMapOuter = new UrlMap($app);
        $urlMapOuter->setMap(array(
            '/foo' => $urlMapInner
        ));

        $response = $urlMapOuter->handle($request = Request::create('/foo/bar?baz=fiz'));
        $urlMapOuter->terminate($request, $response);

        $this->assertEquals('Hello World', $response->getContent());
    }
}

