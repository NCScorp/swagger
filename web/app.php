<?php
use Symfony\Component\HttpFoundation\Request;
require __DIR__.'/../vendor/autoload.php';

$kernel = new AppKernel('prod', false);

//$kernel = new AppCache($kernel);
// When using the HttpCache, you need to call the method in your front controller instead of relying on the configuration parameter
//Request::enableHttpMethodParameterOverride();
$request = Request::createFromGlobals();
Request::setTrustedProxies(array($request->server->get('REMOTE_ADDR')));
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);