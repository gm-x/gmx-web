<?php
$app->add(new \RKA\Middleware\IpAddress(true));

//$app->add(function (\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response, $next) use ($app) {
//    $response = $next($request, $response);
//    /** @var \Monolog\Logger $log */
//    $log = $app->getContainer()->get('log');
//    /** @var \Illuminate\Database\Capsule\Manager $db */
//    $db = $app->getContainer()->get('db');
//
//    $log->debug('queries', $db->getConnection()->getQueryLog());
//
//    return $response;
//});

$app->add(function (\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response, $next) {
    try {
        return $next($request, $response);
    } catch (\GameX\Core\Exceptions\RedirectException $e) {
        return $response->withRedirect($e->getUrl(), $e->getStatus());
    }
});



