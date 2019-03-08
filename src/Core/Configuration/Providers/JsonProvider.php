<?php
namespace GameX\Core\Configuration\Providers;

use \GameX\Core\Configuration\ProviderInterface;
use \GameX\Core\Configuration\Node;
use \GameX\Core\Configuration\Exceptions\CantLoadException;
use \GameX\Core\Configuration\Exceptions\CantSaveException;

class JsonProvider implements ProviderInterface {

    /**
     * @var string
     */
    protected $config;
    
    /**
     * @var string
     */
    protected $default;

    /**
     * @param string|null $config
     * @param string|null $default
     */
    public function __construct($config = null, $default = null) {
        $this->config = $config;
        $this->default = $default ?: dirname(__DIR__) . DIRECTORY_SEPARATOR . 'default.json';
    }

    /**
     * @inheritdoc
     */
    public function load() {
        $default = $this->loadJSON($this->default);
        $config = $this->loadJSON($this->config);

        return new Node(array_replace_recursive($default, $config));
    }

    /**
     * @inheritdoc
     */
    public function save(Node $data) {
        if (!$data->getIsModified() || $this->config === null) {
            return;
        }

        $data = json_encode(
            $data->toArray(),
            JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
        if (file_put_contents($this->config, $data) === false) {
            throw new CantSaveException('Could not write to file ' . $this->config);
        }
    }
    
    /**
     * @param $path
     * @return array|mixed
     * @throws CantLoadException
     */
    protected function loadJSON($path) {
        if (empty($path) || !is_readable($path)) {
            return [];
        }
    
        $content = file_get_contents($path);
        if (empty($content)) {
            throw new CantLoadException('Could not read from file ' . $path);
        }
    
        $data = json_decode($content, true);
        if (json_last_error() != JSON_ERROR_NONE) {
            throw new CantLoadException(json_last_error_msg());
        }
    
        if (!is_array($data)) {
            throw new CantLoadException('Bad format of file ' . $path);
        }
        
        return $data;
    }
}
