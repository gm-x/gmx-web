<?php

use \GameX\Core\Migration;
use \Illuminate\Database\Schema\Blueprint;

class GroupImmunity extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $this->getSchema()->table('groups', function (Blueprint $table) {
            $table->unsignedTinyInteger('immunity')
                ->default(0)
                ->nullable()
                ->after('priority');
        });

    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $this->getSchema()->table('groups', function (Blueprint $table) {
            $table->dropColumn('immunity');
        });
    }
}
