<?php
namespace GameX\Core\Update;

class Actions {
    protected $actions = [];

    public function add(ActionInterface $action) {
        $this->actions[] = $action;
    }

    public function run() {
        foreach ($this->actions as $action) {
            if (!$action->run()) {
                throw new \Exception('Error while updating');
            }
        }
    }
}