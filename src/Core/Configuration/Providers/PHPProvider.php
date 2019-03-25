<?php
namespace GameX\Core\Configuration\Providers;

use \GameX\Core\Configuration\ProviderInterface;
use \GameX\Core\Configuration\Node;
use \GameX\Core\Configuration\Exceptions\CantLoadException;
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
        $default = $this->loadPHP($this->default);
        $config = $this->loadPHP($this->config);

        return new Node(array_replace_recursive($default, $config));
    }

    /**
     * @inheritdoc
     */
    public function save(Node $data) {
        if (!$data->getIsModified() || $this->config === null) {
            return;
        }

        $data = var_export($data->toArray(), true);
        $data = preg_replace("/^([ ]*)(.*)/m", '$1$1$2', $data);
        $array = preg_split("/\r\n|\n|\r/", $data);
        $array = preg_replace(["/\s*array\s\($/", "/\)(,)?$/", "/\s=>\s$/"], [null, ']$1', ' => ['], $array);
        $data = join(PHP_EOL, array_filter(["["] + $array));
        $data = "<?php\nreturn " . $data  . ";\n";
        if (file_put_contents($this->config, $data) === false) {
            throw new CantSaveException('Could not write to file ' . $this->config);
        }
    }

    /**
     * @param $path
     * @return array|mixed
     * @throws CantLoadException
     */
    protected function loadPHP($path) {
        if (empty($path) || !is_readable($path)) {
            return [];
        }

        $data = require $path;

        if (!is_array($data)) {
            throw new CantLoadException('Bad format of file ' . $path);
        }

        return $data;
    }
}
