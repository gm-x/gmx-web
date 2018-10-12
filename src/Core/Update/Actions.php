<?php
namespace GameX\Core\Update;

class Actions {

    /**
     * @var ActionInterface[]
     */
    protected $actions = [];

    /**
     * @param ActionInterface $action
     */
    public function add(ActionInterface $action) {
        $this->actions[] = $action;
    }

    /**
     * @throws \Exception
     */
    public function run() {
        foreach ($this->actions as $action) {
            if (!$action->run()) {
                throw new \Exception('Error while updating');
            }
        }
    }
}