<?php
namespace GameX\Core\Configuration\Providers;

use \GameX\Core\Configuration\ProviderInterface;
use \GameX\Core\Configuration\Node;
use GameX\Models\Preference;
use \GameX\Core\Configuration\Exceptions\CantLoadException;
use \GameX\Core\Configuration\Exceptions\CantSaveException;

class DatabaseProvider implements ProviderInterface {

    /**
     * @inheritdoc
     */
    public function load() {
        $data = [];
        foreach (Preference::all() as $preference) {
            $data[$preference] = $preference->value;
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
            'key' => $key,
            'value' => $value
        ], [
            'value' => $value
        ]);
    }
}