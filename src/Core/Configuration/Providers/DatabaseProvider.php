<?php
namespace GameX\Core\Configuration\Providers;

use \GameX\Core\Configuration\ProviderInterface;
use \GameX\Core\Cache\Cache;
use \GameX\Core\Configuration\Node;
use \GameX\Models\Preference;
use \GameX\Core\Configuration\Exceptions\CantLoadException;
use \GameX\Core\Configuration\Exceptions\CantSaveException;
use \Exception;

class DatabaseProvider implements ProviderInterface {

    /**
     * @var Cache
     */
    protected $cache;

    public function __construct(Cache $cache) {
        $this->cache = $cache;
    }

    /**
     * @inheritdoc
     */
    public function load() {
        try {
            $data = $this->cache->get('preferences');
            return new Node($data);
        } catch (Exception $e) {
            throw new CantLoadException('Could not load from database', 0, $e);
        }
    }

    /**
     * @inheritdoc
     */
    public function save(Node $data) {
        if (!$data->getIsModified()) {
            return;
        }

        /** @var \Illuminate\Database\ConnectionInterface $connection */
        $connection = Preference::getConnectionResolver()->connection();

        $connection->beginTransaction();
        try {
            foreach ($data->keys() as $key) {
                $value = $data->get($key);
                if ($value instanceof Node && $value->getIsModified()) {
                    $this->saveNode($key, $value);
                }
            }
            $this->cache->clear('preferences');
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw new CantSaveException('There is an error while saving configuration', 0, $e);
        }
    }

    /**
     * @param string $key
     * @param Node $value
     */
    private function saveNode($key, Node $value) {
        Preference::updateOrCreate([
            'key' => $key
        ], [
            'value' => $value->toArray()
        ]);
    }
}
