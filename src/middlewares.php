<?php
$app->add(new \RKA\Middleware\IpAddress(true));

$app->add(function (\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response, $next) use ($app) {
    $response = $next($request, $response);
    /** @var \Monolog\Logger $log */
    $log = $app->getContainer()->get('log');
    /** @var \Illuminate\Database\Capsule\Manager $db */
    $db = $app->getContainer()->get('db');

    $log->debug('queries', $db->getConnection()->getQueryLog());

    return $response;
});



