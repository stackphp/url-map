<?php

use Silex\Application;
use CHH\UrlMap;
use Symfony\Component\HttpFoundation\Request;

$app = new Silex\Application;
$app->get('/', function() {
    return "Hello World";
});

$sub = new Silex\Application;
$sub->register(new Silex\Provider\UrlGeneratorServiceProvider);
$sub->get('/', function(Request $req) use ($sub) {
    return "Hello World from Sub App!" . $req->getBaseUrl();
})->bind('root');

$map = new UrlMap($app, array(
    '/foo' => $sub
));

return $map;

