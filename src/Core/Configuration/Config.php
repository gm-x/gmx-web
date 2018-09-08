<?php
namespace GameX\Core\Configuration;

use \GameX\Core\Configuration\Exceptions\CantLoadException;
use \GameX\Core\Configuration\Exceptions\CantSaveException;
use \GameX\Core\Configuration\Exceptions\NotFoundException;

class Config {

    /**
     * @var Node
     */
	protected $node;

    /**
     * @var ProviderInterface
     */
	protected $provider;

    /**
     * Config constructor.
     * @param ProviderInterface $provider
     * @throws CantLoadException
     */
	public function __construct(ProviderInterface $provider) {
	    $this->provider = $provider;
	    $this->node = $provider->load();
    }

    /**
     * @throws CantSaveException
     */
    public function save() {
	    $this->provider->save($this->node);
    }

    /**
     * @param string $key
     * @return Node
     * @throws NotFoundException
     */
	public function getNode($key) {
	    return $this->node->getNode($key);
    }
}
