<?php
use \GameX\Core\Migration;
use \Illuminate\Database\Schema\Blueprint;

class PlayersSessions extends Migration {
    
    /**
     * Do the migration
     */
    public function up() {
        $this->getSchema()
            ->create($this->getTableName(), function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('player_id')->references('id')->on('players');
                $table->unsignedInteger('server_id')->references('id')->on('servers');
                $table->enum('status', [
                    'online',
                    'offline',
                ])->default('offline');
                $table->timestamp('disconnected_at')->nullable();
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
        return 'players_sessions';
    }
}
