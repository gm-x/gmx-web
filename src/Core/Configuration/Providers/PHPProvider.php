<?php
namespace GameX\Core\Configuration\Providers;

use \GameX\Core\Configuration\ProviderInterface;
use \GameX\Core\Configuration\Node;
use \GameX\Core\Configuration\Exceptions\CantSaveException;

class PHPProvider implements ProviderInterface {

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
        $this->default = $default ?: dirname(__DIR__) . DIRECTORY_SEPARATOR . 'default.php';
    }

    /**
     * @inheritdoc
     */
    public function load() {
        $default = require $this->default;
        $config = require $this->config;

        return new Node(array_replace_recursive($default, $config));
    }

    /**
     * @inheritdoc
     */
    public function save(Node $data) {
        if (!$data->getIsModified() || $this->config === null) {
            return;
        }

        $data = "<?php\nreturn " . var_export($data->toArray(), true);
        if (file_put_contents($this->config, $data) === false) {
            throw new CantSaveException('Could not write to file ' . $this->config);
        }
    }
}
