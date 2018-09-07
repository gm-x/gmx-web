<?php
namespace GameX\Core\Configuration;

use \GameX\Core\Configuration\Exceptions\CantLoadException;
use \GameX\Core\Configuration\Exceptions\CantSaveException;

interface ProviderInterface {
    /**
     * @return Node
     * @throws CantLoadException
     */
    public function load();

    /**
     * @param Node $data
     * @throws CantSaveException
     */
    public function save(Node $data);
}