<?php
use \GameX\Core\Migration;
use \Illuminate\Database\Schema\Blueprint;

class Tasks extends Migration{

    /**
     * Do the migration
     */
    public function up() {
        $this->getSchema()
            ->create($this->getTableName(), function (Blueprint $table) {
                $table->increments('id');
                $table->string('key', 255);
                $table->unsignedTinyInteger('key_id')->nullable();
                $table->text('data');
                $table->unsignedTinyInteger('status')->default(0);
                $table->unsignedTinyInteger('retries')->default(0);
                $table->unsignedTinyInteger('max_retries')->default(0);
                $table->unsignedInteger('execute_at');
                $table->timestamps();
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
        return 'tasks';
    }
}
