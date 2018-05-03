<?php
require __DIR__ . '/vendor/autoload.php';

$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection([
    'driver' => 'mysql',
    'host' => '127.0.0.1',
    'database' => 'test',
    'username' => 'root',
    'password' => '',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

use Cartalyst\Sentinel\Native\SentinelBootstrapper;
$bootsrap = new SentinelBootstrapper();
$sentinel = $bootsrap->createSentinel();
$credentials = [
    'email'    => 'test@example.com',
    'password' => 'foobar',
];
//$sentinel->register($credentials);

$user = $sentinel->getUserRepository()->findByCredentials($credentials);
//$activation = $sentinel->getActivationRepository()->create($user);

$code = 'sxEuC7uFCTEzaMnyo8T8CgeCphWq8shl';
//$sentinel->getActivationRepository()->complete($user, $code);
$sentinel->authenticate($credentials);
