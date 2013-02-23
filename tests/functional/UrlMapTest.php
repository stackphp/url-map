<?php

namespace Stack\Test;

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
        $app = new CallableHttpKernel(function (Request $req) {
            return new Response("Fallback!");
        });

        $urlMap = new UrlMap($app);
        $urlMap->setMap([
            '/foo' => new CallableHttpKernel(function (Request $req) {
                return new Response('foo');
            }),
        ]);

        $req = Request::create('/foo');
        $resp = $urlMap->handle($req);

        $this->assertEquals('foo', $resp->getContent());
    }

    public function testOverridesPathInfo()
    {
        $app = new CallableHttpKernel(function (Request $req) {
            return new Response("Fallback!");
        });

        $urlMap = new UrlMap($app);
        $urlMap->setMap([
            '/foo' => new CallableHttpKernel(function (Request $req) {
                $this->assertEquals('/', $req->getPathinfo());
                $this->assertEquals('/foo', $req->attributes->get(UrlMap::ATTR_PREFIX));
                $this->assertEquals('/foo', $req->getBaseUrl());

                return new Response("Hello World");
            }),
        ]);

        $resp = $urlMap->handle(Request::create('/foo?bar=baz'));

        $this->assertEquals('Hello World', $resp->getContent());
    }
}

