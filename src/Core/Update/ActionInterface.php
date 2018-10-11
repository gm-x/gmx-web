<?php
namespace GameX\Core\Update;

interface ActionInterface {
    public function __construct($source, $destination);

    public function run();
}