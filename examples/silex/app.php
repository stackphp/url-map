<?php

use Silex\Application;
use CHH\UrlMap;

$app = new Silex\Application;
$app->get('/', function() {
    return "Hello World";
});

$sub = new Silex\Application;
$sub->get('/', function() {
    return "Hello World from Sub App!";
});

$map = new UrlMap($app, array(
    '/foo' => $sub
));

return $map;

