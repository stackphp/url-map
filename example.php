<?php

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

require 'vendor/autoload.php';

$app = new Silex\Application();

$app->get('/', function() {
    return "Main application!";
});

$blog = new Silex\Application();

$blog->get('/', function() {
    return "This is the blog!";
});

$stack = (new Stack\Stack)
    ->push('Stack\UrlMap', array('/blog' => $blog));

$app = $stack->resolve($app);

$request = Request::createFromGlobals();
$response = $app->handle($request)->send();

$app->terminate($request, $response);
