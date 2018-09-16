<?php
use \GameX\Core\Migration;
use \Illuminate\Database\Schema\Blueprint;

class Preferences extends Migration {

    /**
     * Do the migration
     */
    public function up() {
        $this->getSchema()
            ->create($this->getTableName(), function (Blueprint $table) {
                $table->string('key')->unique();
                $table->text('value');
            });
    }

    /**
     * Undo the migration
     */
    public function down() {
        $this->getSchema()->drop($this->getTableName());
    }

    /**
     * @return string
     */
    private function getTableName() {
        return 'preferences';
    }
}
