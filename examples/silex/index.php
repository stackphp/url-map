<?php

require(__DIR__ . '/../../vendor/autoload.php');

use Symfony\Component\HttpFoundation\Request;

$app = require(__DIR__ . '/app.php');
$app->handle(Request::createFromGlobals())->send();

