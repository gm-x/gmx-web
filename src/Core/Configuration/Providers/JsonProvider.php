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
    protected $path;

    /**
     * @param string|null $path
     */
    public function __construct($path = null) {
        $this->path = $path !== null
            ? (string) $path
            : dirname(__DIR__) . DIRECTORY_SEPARATOR . 'default.json';
    }

    /**
     * @inheritdoc
     */
    public function load() {
        if (!is_readable($this->path)) {
            throw new CantLoadException('Could not open file ' . $this->path);
        }

        $content = file_get_contents($this->path);
        if (!$content) {
            throw new CantLoadException('Could not read from file ' . $this->path);
        }

        $data = json_decode($content, true);
        if (json_last_error() != JSON_ERROR_NONE) {
            throw new CantLoadException(json_last_error_msg());
        }

        if (!is_array($data)) {
            throw new CantLoadException('Bad format of file ' . $this->path);
        }

        return new Node($data);
    }

    /**
     * @inheritdoc
     */
    public function save(Node $data) {
        if (!$data->getIsModified()) {
            return;
        }

        $data = json_encode(
            $data->toArray(),
            JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
        if (file_put_contents($this->path, $data) === false) {
            throw new CantSaveException('Could not write to file ' . $this->path);
        }
    }

    /**
     * @param string $path
     * @return self
     */
    public function setPath($path) {
        $this->path = (string) $path;
        return $this;
    }
}