<?php
$app->add(new \RKA\Middleware\IpAddress(true));

$app->add(function (\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response, callable $next) use ($app) {
    $response = $next($request, $response);
    
    /** @var GameX\Core\Configuration\Config $config */
    $config = $app->getContainer()->get('config');

    if ($config->getNode('log')->get('queries', false)) {
        /** @var \Monolog\Logger $log */
        $logger = $app->getContainer()->get('log');
        /** @var \Illuminate\Database\Capsule\Manager $db */
        $db = $app->getContainer()->get('db');
        
        $queries = $db->getConnection()->getQueryLog();
        foreach ($queries as $query) {
            $log = sprintf('Query (%s): %s', $query['time'], $query['query']);
            $logger->debug($log, $query['bindings']);
        }
    }

    return $response;
});

$app->add(function (\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response, callable $next) {
    try {
        return $next($request, $response);
    } catch (\GameX\Core\Exceptions\RedirectException $e) {
        return $response->withRedirect($e->getUrl(), $e->getStatus());
    }
});

$app->add(function (\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response, callable $next) {
    $uri = $request->getUri();
    $path = $uri->getPath();
    if ($path != '/' && substr($path, -1) == '/') {
        // permanently redirect paths with a trailing slash
        // to their non-trailing counterpart
        $uri = $uri->withPath(substr($path, 0, -1));
        
        if($request->getMethod() == 'GET') {
            return $response->withRedirect((string)$uri, 301);
        }
        else {
            return $next($request->withUri($uri), $response);
        }
    }
    
    return $next($request, $response);
});


