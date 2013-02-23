<?php

use Silex\Application;
use CHH\UrlMap;
use Symfony\Component\HttpFoundation\Request;

$app = new Silex\Application;
$app->get('/', function () {
    return "Hello World";
});

$sub = new Silex\Application;
$sub->register(new Silex\Provider\UrlGeneratorServiceProvider);
$sub->get('/', function (Request $request) use ($sub) {
    return "Hello World from Sub App!" . $request->getBaseUrl() . "\n"
        . $sub['url_generator']->generate('root');
})->bind('root');

$map = new UrlMap($app, [
    '/foo' => $sub,
]);

return $map;
