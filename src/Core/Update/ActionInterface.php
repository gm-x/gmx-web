<?php
namespace GameX\Core\Update;

interface ActionInterface {

    /**
     * @return bool
     */
    public function run();
}