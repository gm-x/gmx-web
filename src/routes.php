<?php
$app->group('', function() {
    /** @var \Slim\App $this */
    $controller = new \GameX\Controllers\IndexController($this);

    $this->get('/', $controller->registerAction('index'));
});
