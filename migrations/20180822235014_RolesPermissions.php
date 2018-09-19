<?php
use \GameX\Core\Migration;
use \Illuminate\Database\Schema\Blueprint;

class RolesPermissions extends Migration {
    /**
     * Do the migration
     */
    public function up() {
        $this->getSchema()
            ->create($this->getTableName(), function (Blueprint $table) {
                $table->unsignedInteger('role_id')->references('id')->on('roles');
                $table->unsignedInteger('permission_id')->references('id')->on('permissions');
                $table->unsignedInteger('resource')->nullable();
                $table->unsignedTinyInteger('access')->nullable()->default('0');
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
        return 'roles_permissions';
    }
}
