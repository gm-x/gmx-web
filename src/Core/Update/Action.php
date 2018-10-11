<?php
namespace GameX\Core\Update;

abstract class Action implements ActionInterface {
    protected $source;
    protected $destination;

    public function __construct($source, $destination) {
        $this->source = $source;
        $this->destination = $destination;
    }
}