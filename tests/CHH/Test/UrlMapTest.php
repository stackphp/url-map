<?php

namespace CHH\Test;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use CHH\UrlMap;
use Stack\CallableHttpKernel;

class UrlMapTest extends \PHPUnit_Framework_TestCase
{
    function test()
    {
        $app = new CallableHttpKernel(function(Request $req) {
            return new Response("Fallback!");
        });

        $urlMap = new UrlMap($app);
        $urlMap->setMap(array(
            '/foo' => new CallableHttpKernel(function(Request $req) {
                return new Response('foo');
            })
        ));

        $req = Request::create('/foo');
        $resp = $urlMap->handle($req);

        $this->assertEquals('foo', $resp->getContent());
    }

    function testOverridesPathInfo()
    {
        $test = $this;

        $app = new CallableHttpKernel(function(Request $req) {
            return new Response("Fallback!");
        });

        $urlMap = new UrlMap($app);
        $urlMap->setMap(array(
            '/foo' => new CallableHttpKernel(function(Request $req) use ($test) {
                $test->assertEquals('/', $req->getPathinfo());
                $test->assertEquals('/foo', $req->attributes->get(UrlMap::ATTR_PREFIX));
                $test->assertEquals('/foo', $req->getBaseUrl());

                return new Response("Hello World");
            })
        ));

        $resp = $urlMap->handle(Request::create('/foo?bar=baz'));

        $this->assertEquals('Hello World', $resp->getContent());
    }
}

