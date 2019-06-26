<?php
namespace GameX\Core\Update\Actions;

use \GameX\Core\Update\ActionInterface;

class ActionDeleteFile implements ActionInterface {

    /**
     * @var string
     */
    protected $destination;

    /**
     * @param string $destination
     */
    public function __construct($destination) {
        $this->destination = $destination;
    }

    /**
     * @inheritdoc
     */
    public function run() {
    	return is_file($this->destination)
            ? unlink($this->destination)
		    : true;
    }
}
