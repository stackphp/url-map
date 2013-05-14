# URL Map Stack Middleware

The `UrlMap` class takes an array mapping paths to apps and dispatches
accordingly. This class is insertable into a Middleware Stack Builder, like
[stack/builder](http://github.com/stackphp/builder).

## Install

Install with Composer:

    % curl -sS https://getcomposer.org/installer | php
    % php composer.phar require chh/url-map:~1.0@dev

## Example

Let's say we have a Silex app and want to map an blogging app which
implements HttpKernelInterface at the sub path `/blog`:

```php
<?php

use Symfony\Component\HttpFoundation\Request;
use Silex\Application;

$app = new Application();
$app->get('/', function () {
    return "Main Application!";
});

$blog = new Application();
$blog->get('/', function () {
    return "This is the blog!";
});

$map = [
    "/blog" => $blog
];

$app = (new Stack\Builder())
        ->push('Stack\UrlMap', $map)
        ->resolve($app);

$request = Request::createFromGlobals();

$response = $app->handle($request);
$response->send();

$app->terminate($request, $response);
```

If you now navigate to `/blog` you should see `This is the blog!` in your
browser.

The `UrlMap` overrides `SCRIPT_NAME`, `SCRIPT_FILENAME` and `PHP_SELF`
to point at the mapped path. This also makes sure that the path is
stripped from the path info.

This also means that apps which use the Symfony Routing component for
Routing and URL Generation don't need any adaptions.

Apps using other means for routing should prepend the return value of the
request's `getBaseUrl()` method to generated URLs. The URL Map also sets a
`stack.url_map.prefix` request attribute, which can be used if you don't want
to rely on the request base URL.
